<?php
/**
 * This file is part of the Magebit_AkeneoConnector package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_BankTransfer
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

// @codingStandardsIgnoreFile

declare(strict_types=1);

namespace Magebit\AkeneoConnector\Job;

use Akeneo\Connector\Block\Adminhtml\System\Config\Form\Field\Configurable as TypeField;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Job\Product as JobProduct;
use Akeneo\Connector\Model\Source\Edition;
use Exception;
use Magento\Catalog\Model\Product as BaseProductModel;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Statement\Pdo\Mysql;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Zend_Db_Expr as Expr;
use Zend_Db_Statement_Exception;

class Product extends JobProduct
{
    /**
     * Import the medias
     *
     * @return void
     * @throws LocalizedException
     * @throws FileSystemException
     * @throws Zend_Db_Statement_Exception
     * @throws Exception
     */
    public function importMedia(): void
    {
        if (!$this->configHelper->isMediaImportEnabled()) {
            $this->setStatus(true);
            $this->jobExecutor->setMessage(__('Media import is not enabled'), $this->logger);

            return;
        }

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tableName */
        $tmpTable = $this->entitiesHelper->getTableName($this->jobExecutor->getCurrentJob()->getCode());

        $gallery = $this->configHelper->getMediaImportGalleryColumns();
        $variant = $this->configHelper->getMediaImportVariantColumns();
        $gallery = array_merge($gallery, $variant);

        if (empty($gallery)) {
            $this->setStatus(true);
            $this->jobExecutor->setMessage(__('Akeneo Images Attributes is empty'), $this->logger);

            return;
        }

        $gallery = array_unique($gallery);

        $table = $this->entitiesHelper->getTable('catalog_product_entity');
        $columnIdentifier = $this->entitiesHelper->getColumnIdentifier($table);
        /** @var array $data */
        $data = [
            $columnIdentifier => '_entity_id',
            'sku'             => 'identifier',
            'parent'          => 'parent'
        ];

        $stores = $this->storeHelper->getAllStores();
        /** @var string[] $dataToImport */
        $dataToImport = [];
        $valueFound = false;
        foreach ($gallery as $image) {
            if (!$connection->tableColumnExists($tmpTable, strtolower($image))) {
                // If not exist, check for each store if the field exist
                /**
                 * @var string $suffix
                 * @var array $storeData
                 */
                foreach ($stores as $suffix => $storeData) {
                    if (!$connection->tableColumnExists(
                        $tmpTable,
                        strtolower($image) . self::SUFFIX_SEPARATOR . $suffix
                    )) {
                        continue;
                    }
                    $valueFound = true;
                    $data[$image . self::SUFFIX_SEPARATOR . $suffix] = strtolower($image) . self::SUFFIX_SEPARATOR . $suffix;
                    $dataToImport[strtolower($image) . self::SUFFIX_SEPARATOR . $suffix] = $suffix;
                }
                if (!$valueFound) {
                    $this->jobExecutor->setMessage(
                        __('Info: No value found in the current batch for the attribute %1', $image),
                        $this->logger
                    );
                }
                continue;
            }
            // Global image
            $data[$image] = strtolower($image);
            $dataToImport[$image] = null;
        }

        $rowIdExists = $this->entitiesHelper->rowIdColumnExists($table);
        if ($rowIdExists) {
            $data[$columnIdentifier] = 'p.row_id';
        }

        $select = $connection->select()->from($tmpTable, $data);

        if ($rowIdExists) {
            $this->entities->addJoinForContentStaging($select, []);
        }

        /** @var Mysql $query */
        $query = $connection->query($select);

        /** @var Attribute $galleryAttribute */
        $galleryAttribute = $this->configHelper->getAttribute(
            BaseProductModel::ENTITY,
            'media_gallery'
        );
        $galleryTable = $this->entitiesHelper->getTable(
            'catalog_product_entity_media_gallery'
        );
        $galleryValueTable = $this->entitiesHelper->getTable(
            'catalog_product_entity_media_gallery_value'
        );
        $galleryEntityTable = $this->entitiesHelper->getTable(
            'catalog_product_entity_media_gallery_value_to_entity'
        );
        $productImageTable = $this->entitiesHelper->getTable(
            'catalog_product_entity_varchar'
        );
        /** @var string[] $medias */
        $medias = [];

        /** @var array $row */
        while (($row = $query->fetch())) {
            $positionCounter = 0;
            $files = [];
            /**
             * @var string $image
             * @var string $suffix
             */
            foreach ($dataToImport as $image => $suffix) {
                if (!isset($row[$image])) {
                    continue;
                }

                if (!$row[$image]) {
                    continue;
                }

                // Skip if image attribute is common and current record is child product
                if (!in_array($image, $this->configHelper->getMediaImportVariantColumns())
                    && isset($row['parent'])
                ) {
                    continue;
                }

                if (!isset($medias[$row[$image]])) {
                    $medias[$row[$image]] = $this->akeneoClient->getProductMediaFileApi()->get(
                        $row[$image]
                    );
                }
                $name = $this->entitiesHelper->formatMediaName(basename($medias[$row[$image]]['code']));
                $filePath = $this->configHelper->getMediaFullPath($name);
                /** @var bool|string[] $databaseRecords */
                $databaseRecords = false;

                if (!$this->configHelper->mediaFileExists($filePath)) {
                    $binary = $this->akeneoClient->getProductMediaFileApi()->download($row[$image]);
                    $imageContent = $binary->getBody()->getContents();
                    $this->configHelper->saveMediaFile($filePath, $imageContent);
                }

                $file = $this->configHelper->getMediaFilePath($name);

                /** @var int $valueId */
                $valueId = $connection->fetchOne(
                    $connection->select()->from($galleryTable, ['value_id'])->where('value = ?', $file)
                );

                if (!$valueId) {
                    /** @var int $valueId */
                    $valueId = $connection->fetchOne(
                        $connection->select()->from($galleryTable, [new Expr('MAX(`value_id`)')])
                    );
                    ++$valueId;
                }

                $data = [
                    'value_id'     => $valueId,
                    'attribute_id' => $galleryAttribute->getId(),
                    'value'        => $file,
                    'media_type'   => ImageEntryConverter::MEDIA_TYPE_CODE,
                    'disabled'     => 0,
                ];
                $connection->insertOnDuplicate($galleryTable, $data, array_keys($data));

                $data = [
                    'value_id'        => $valueId,
                    $columnIdentifier => $row[$columnIdentifier],
                ];
                $connection->insertOnDuplicate($galleryEntityTable, $data, array_keys($data));

                /**
                 * @var string  $storeSuffix
                 * @var array $storeArray
                 */
                foreach ($stores as $storeSuffix => $storeArray) {
                    /** @var array $store */
                    foreach ($storeArray as $store) {
                        $disabled = 0;
                        if ($suffix) {
                            $storeIsInEnabledStores = false;
                            if ($suffix !== $storeSuffix) {
                                $disabled = 1;
                                // Disable image for this store, only if this store is not in enabled stores list
                                /** @var array $enabledStores */
                                foreach ($stores[$suffix] as $enabledStores) {
                                    if ($enabledStores['store_code'] === $store['store_code']) {
                                        $storeIsInEnabledStores = true;
                                    }
                                }

                                if ($storeIsInEnabledStores) {
                                    continue;
                                }
                            }
                        }
                        // Get potential record_id from gallery value table
                        /** @var int $databaseRecords */
                        $databaseRecords = $connection->fetchOne(
                            $connection->select()->from($galleryValueTable, [new Expr('MAX(`record_id`)')])->where(
                                'value_id = ?',
                                $valueId
                            )->where(
                                'store_id = ?',
                                $store['store_id']
                            )->where(
                                $columnIdentifier . ' = ?',
                                $row[$columnIdentifier]
                            )
                        );

                        $recordId = 0;
                        if (!empty($databaseRecords)) {
                            $recordId = $databaseRecords;
                        }

                        /** @var string[] $data */
                        $data = [
                            'value_id' => $valueId,
                            'store_id' => $store['store_id'],
                            $columnIdentifier => $row[$columnIdentifier],
                            'label' => '',
                            'position' => $positionCounter,
                            'disabled' => $disabled,
                        ];

                        $positionCounter++;

                        if ($recordId != 0) {
                            $data['record_id'] = $recordId;
                        }
                        $connection->insertOnDuplicate($galleryValueTable, $data, array_keys($data));

                        $columns = $this->configHelper->getMediaImportImagesColumns();

                        foreach ($columns as $column) {
                            $columnName = $column['column'];
                            $mappings = $this->configHelper->getWebsiteMapping();
                            /** @var string|null $locale */
                            $locale = null;
                            /** @var string|null $scope */
                            $scope = null;
                            if ($suffix) {
                                $columnName .= self::SUFFIX_SEPARATOR . $suffix;
                                if (str_contains($suffix, '-')) {
                                    $suffixs = explode('-', $suffix);
                                    if (isset($suffixs[0])) {
                                        $locale = $suffixs[0];
                                    }
                                    if (isset($suffixs[1])) {
                                        $scope = $suffixs[1];
                                    }
                                } elseif (str_contains($suffix, '_')) {
                                    $locale = $suffix;
                                } else {
                                    $scope = $suffix;
                                }
                            }

                            foreach ($mappings as $mapping) {
                                if (($columnName !== $image) || ((isset($scope, $locale)) && ($store['website_code'] !== $mapping['website'] || $store['channel_code'] !== $scope || $store['lang'] !== $locale))
                                    || ((isset($scope)) && ($store['website_code'] !== $mapping['website'] || $store['channel_code'] !== $scope))
                                    || ((isset($locale)) && ($store['website_code'] !== $mapping['website'] || $store['lang'] !== $locale))
                                ) {
                                    continue;
                                }

                                /** @var string[] $data */
                                $data = [
                                    'attribute_id'    => $column['attribute'],
                                    'store_id'        => $store['store_id'],
                                    $columnIdentifier => $row[$columnIdentifier],
                                    'value'           => $file,
                                ];

                                $connection->insertOnDuplicate($productImageTable, $data, array_keys($data));
                            }
                        }
                    }
                }

                $files[] = $file;
            }

            $cleaner = $connection->select()->from($galleryTable, ['value_id'])->where('value NOT IN (?)', $files);

            $connection->delete(
                $galleryEntityTable,
                [
                    'value_id IN (?)' => $cleaner,
                    $columnIdentifier . ' = ?' => $row[$columnIdentifier],
                ]
            );
            // Delete old value association with the imported product
            $connection->delete(
                $galleryValueTable,
                [
                    'value_id IN (?)'          => $cleaner,
                    $columnIdentifier . ' = ?' => $row[$columnIdentifier],
                ]
            );
        }
    }

    /**
     * Create configurable products
     *
     * @return void
     * @throws LocalizedException
     */
    public function addRequiredData(): void
    {
        $family = $this->getFamily();
        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($this->jobExecutor->getCurrentJob()->getCode());
        $edition = $this->configHelper->getEdition();

        // If family is grouped, create grouped products
        if (($edition === Edition::SERENITY || $edition === Edition::GREATER_OR_FIVE || $edition === Edition::GROWTH)
            && $this->entitiesHelper->isFamilyGrouped($family)) {
            $connection->addColumn(
                $tmpTable,
                '_type_id',
                [
                    'type' => 'text',
                    'length' => 255,
                    'default' => 'grouped',
                    'COMMENT' => ' ',
                    'nullable' => false,
                ]
            );
        } else {
            $connection->addColumn(
                $tmpTable,
                '_type_id',
                [
                    'type' => 'text',
                    'length' => 255,
                    'default' => 'simple',
                    'COMMENT' => ' ',
                    'nullable' => false,
                ]
            );
        }
        $connection->addColumn(
            $tmpTable,
            '_options_container',
            [
                'type' => 'text',
                'length' => 255,
                'default' => 'container2',
                'COMMENT' => ' ',
                'nullable' => false,
            ]
        );
        $connection->addColumn(
            $tmpTable,
            '_tax_class_id',
            [
                'type' => 'integer',
                'length' => 11,
                'default' => 0,
                'COMMENT' => ' ',
                'nullable' => false,
            ]
        ); // None
        $connection->addColumn(
            $tmpTable,
            '_attribute_set_id',
            [
                'type' => 'integer',
                'length' => 11,
                'default' => 4,
                'COMMENT' => ' ',
                'nullable' => false,
            ]
        ); // Default
        $connection->addColumn(
            $tmpTable,
            '_visibility',
            [
                'type' => 'integer',
                'length' => 11,
                'default' => Visibility::VISIBILITY_BOTH,
                'COMMENT' => ' ',
                'nullable' => false,
            ]
        );
        $connection->addColumn(
            $tmpTable,
            '_status',
            [
                'type' => 'integer',
                'length' => 11,
                'default' => 2,
                'COMMENT' => ' ',
                'nullable' => false,
            ]
        ); // Disabled

        if (!$connection->tableColumnExists($tmpTable, 'is_returnable')) {
            $connection->addColumn(
                $tmpTable,
                'is_returnable',
                [
                    'type' => 'integer',
                    'length' => 11,
                    'default' => 2,
                    'COMMENT' => ' ',
                    'nullable' => false,
                ]
            );
        }

        if (!$connection->tableColumnExists($tmpTable, 'url_key') && $this->configHelper->isUrlGenerationEnabled()) {
            $connection->addColumn(
                $tmpTable,
                'url_key',
                [
                    'type' => 'text',
                    'length' => 255,
                    'default' => '',
                    'COMMENT' => ' ',
                    'nullable' => false,
                ]
            );

            $connection->update($tmpTable, ['url_key' => new Expr('LOWER(`name`)')], 'parent IS NULL');
            $connection->update($tmpTable, ['url_key' => new Expr('LOWER(`identifier`)')], 'parent IS NOT NULL');
        }

        /** @var string|null $groupColumn */
        $groupColumn = null;
        if ($connection->tableColumnExists($tmpTable, 'parent')) {
            $groupColumn = 'parent';
        }
        if ($connection->tableColumnExists($tmpTable, 'groups') && !$groupColumn) {
            $groupColumn = 'groups';
        }

        if ($groupColumn) {
            $connection->update(
                $tmpTable,
                [
                    '_visibility' => new Expr(
                        'IF(`' . $groupColumn . '` <> "", ' . Visibility::VISIBILITY_NOT_VISIBLE . ', ' . Visibility::VISIBILITY_BOTH . ')'
                    ),
                ]
            );
        }
        /** @var string $productMappingAttribute */
        $productMappingAttribute = $this->configHelper->getMappingAttribute();
        if ($connection->tableColumnExists($tmpTable, $productMappingAttribute)) {
            /** @var string $types */
            $types = $connection->quote($this->allowedTypeId);
            $connection->update(
                $tmpTable,
                [
                    '_type_id' => new Expr(
                        "IF($productMappingAttribute IN ($types), $productMappingAttribute, 'simple')"
                    ),
                ]
            );
        }

        if ($connection->tableColumnExists($tmpTable, 'enabled')) {
            $connection->update(
                $tmpTable,
                ['_status' => new Expr('IF(`enabled` <> 1, 2, 1)')],
                ['_type_id = ?' => 'simple']
            );
        }

        /** @var string|array $matches */
        $matches = $this->configHelper->getAttributeMapping();
        if (!is_array($matches)) {
            return;
        }

        /** @var array $stores */
        $stores = $this->storeHelper->getAllStores();

        /** @var array $match */
        foreach ($matches as $match) {
            if (!isset($match['akeneo_attribute'], $match['magento_attribute'])) {
                continue;
            }

            /** @var string $pimAttribute */
            $pimAttribute = $match['akeneo_attribute'];
            /** @var string $magentoAttribute */
            $magentoAttribute = $match['magento_attribute'];

            $this->entitiesHelper->copyColumn($tmpTable, $pimAttribute, $magentoAttribute, $family);

            /**
             * @var string $local
             * @var string $affected
             */
            foreach ($stores as $local => $affected) {
                $this->entitiesHelper->copyColumn(
                    $tmpTable,
                    $pimAttribute . '-' . $local,
                    $magentoAttribute . '-' . $local,
                    $family
                );
            }
        }
    }

    /**
     * Description createConfigurable function
     *
     * @return void
     * @throws LocalizedException
     */
    public function createConfigurable(): void
    {
        if ($this->entitiesHelper->isFamilyGrouped($this->getFamily())) {
            return;
        }

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($this->jobExecutor->getCurrentJob()->getCode());

        $connection->addColumn($tmpTable, '_children', 'text');
        $connection->addColumn(
            $tmpTable,
            '_axis',
            [
                'type' => 'text',
                'length' => 255,
                'default' => '',
                'COMMENT' => ' ',
            ]
        );

        // No product models were imported during this import, skip
        if (!$connection->isTableExists($this->entitiesHelper->getTableName('product_model'))) {
            return;
        }
        /** @var string|null $groupColumn */
        $groupColumn = null;
        if ($connection->tableColumnExists($tmpTable, 'parent')) {
            $groupColumn = 'parent';
        }
        if (!$groupColumn && $connection->tableColumnExists($tmpTable, 'groups')) {
            $groupColumn = 'groups';
        }
        if (!$groupColumn) {
            $this->setStatus(false);
            $this->jobExecutor->setMessage(__('Columns groups or parent not found'), $this->logger);

            return;
        }

        /** @var string $productModelTable */
        $productModelTable = $this->entitiesHelper->getTableName('product_model');

        if ($connection->tableColumnExists($productModelTable, 'parent')) {
            $select = $connection->select()->from(false, [$groupColumn => 'v.parent'])->joinInner(
                ['v' => $productModelTable],
                'v.parent IS NOT NULL AND e.' . $groupColumn . ' = v.code',
                []
            );

            $connection->query(
                $connection->updateFromSelect($select, ['e' => $tmpTable])
            );
        }

        /** @var array $data */
        $data = [
            'identifier' => 'v.code',
            '_type_id' => new Expr('"configurable"'),
            '_options_container' => new Expr('"container1"'),
            '_axis' => 'v.axis',
            'family' => $connection->tableColumnExists(
                $productModelTable,
                'family'
            ) ? 'v.family' : 'e.family',
            'categories' => 'v.categories',
        ];

        if ($this->configHelper->isUrlGenerationEnabled()) {
            $data['url_key'] = new Expr('LOWER(`v`.`name`)');
        }

        /** @var array $columnsModel */
        $columnsModel = array_keys($connection->describeTable($productModelTable));
        foreach ($columnsModel as $columnModel) {
            if (!isset($data[$columnModel])) {
                $data[$columnModel] = 'v.' . $columnModel;
            }
        }

        /** @var string[] $associationTypes */
        $associationTypes = $this->configHelper->getAssociationTypes();
        /** @var string[] $associationNames */
        foreach ($associationTypes as $associationNames) {
            if (empty($associationNames)) {
                continue;
            }
            /** @var string $associationName */
            foreach ($associationNames as $associationName) {
                if (!empty($associationName)
                    && $connection->tableColumnExists($productModelTable, $associationName)
                    && $connection->tableColumnExists($tmpTable, $associationName)
                ) {
                    $data[$associationName] = sprintf('v.%s', $associationName);
                }
            }
        }

        /** @var string $additional */
        $additional = $this->scopeConfig->getValue(ConfigHelper::PRODUCT_CONFIGURABLE_ATTRIBUTES);
        /** @var mixed[] $additional */
        $additional = $this->jsonSerializer->unserialize($additional);
        if (!is_array($additional)) {
            $additional = [];
        }

        /** @var array $stores */
        $stores = $this->storeHelper->getAllStores();

        /** @var array $attribute */
        foreach ($additional as $attribute) {
            if (!isset($attribute['attribute'], $attribute['value'], $attribute['type'])) {
                continue;
            }

            /** @var string $name */
            $name = strtolower($attribute['attribute']);
            /** @var string $value */
            $value = $attribute['value'];
            /** @var string $type */
            $type = $attribute['type'];
            /** @var array $columns */
            $columns = [trim($name)];

            /**
             * @var string $local
             * @var string $affected
             */
            foreach ($stores as $local => $affected) {
                $columns[] = trim($name) . '-' . $local;
            }

            /** @var array $column */
            foreach ($columns as $column) {
                if ($column === 'enabled' && $connection->tableColumnExists($tmpTable, 'enabled')) {
                    $column = '_status';
                    if ($value === self::PIM_PRODUCT_STATUS_DISABLED) {
                        $value = self::MAGENTO_PRODUCT_STATUS_DISABLED;
                    }
                }

                if ($type !== TypeField::TYPE_MAPPING && !$connection->tableColumnExists($tmpTable, $column)) {
                    continue;
                }

                if ($type === TypeField::TYPE_VALUE) {
                    $data[$column] = new Expr('"' . $value . '"');
                }

                if ($type === TypeField::TYPE_QUERY) {
                    $data[$column] = new Expr($value);
                }

                if ($type === TypeField::TYPE_SIMPLE) {
                    $data[$column] = 'e.' . $column;
                }

                if ($type === TypeField::TYPE_MAPPING) {
                    if (!$connection->tableColumnExists($productModelTable, $column)) {
                        continue;
                    }
                    /** @var string $mapping */
                    $mapping = $value;
                    if (($pos = strpos($column, '-')) !== false) {
                        $mapping = $value . '-' . substr($column, $pos + 1);
                    }
                    if (!$connection->tableColumnExists($tmpTable, $mapping)) {
                        $connection->addColumn(
                            $tmpTable,
                            $mapping,
                            [
                                'type' => 'text',
                                'length' => 255,
                                'default' => null,
                                'COMMENT' => ' ',
                                'nullable' => true,
                            ]
                        );
                    }
                    $data[$mapping] = 'v.' . $column;
                }
            }
        }

        /** @var Select $configurable */
        $configurable = $connection->select()->from(['v' => $productModelTable], $data)->joinLeft(
            ['e' => $tmpTable],
            'v.code = ' . 'e.' . $groupColumn,
            []
        )->where('v.parent IS NULL')->group('v.code');

        /** @var string $query */
        $query = $connection->insertFromSelect($configurable, $tmpTable, array_keys($data));

        $connection->query($query);

        // Update _children column if possible
        /** @var Select $childList */
        $childList = $connection->select()->from(
            ['v' => $productModelTable],
            ['v.identifier', '_children' => new Expr('GROUP_CONCAT(e.identifier SEPARATOR ",")')]
        )->joinInner(['e' => $tmpTable], 'v.code = ' . 'e.' . $groupColumn, [])->group('v.identifier');

        $queryChilds = $connection->query($childList);

        /** @var array $row */
        while (($row = $queryChilds->fetch())) {
            /** @var array $values */
            $values = [
                'identifier' => $row['identifier'],
                '_children' => $row['_children'],
            ];

            $connection->insertOnDuplicate(
                $tmpTable,
                $values,
                []
            );
        }
    }
}
