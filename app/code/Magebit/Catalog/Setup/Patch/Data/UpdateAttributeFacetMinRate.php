<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Setup\Patch\Data;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class UpdateAttributeFacetMinRate implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeCollectionFactory $attributeCollection
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly AttributeCollectionFactory $attributeCollection
    ) {
    }

    /**
     * @return void
     */
    public function apply(): void
    {
        $this->updateAttributeFacetMinRateColumn();
        $this->updateAttributeFacetMinRateValues();
    }

    /**
     * @return void
     */
    public function updateAttributeFacetMinRateColumn(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $table  = $this->moduleDataSetup->getTable('catalog_eav_attribute');
        $this->moduleDataSetup->getConnection()->addColumn(
            $table,
            'facet_min_coverage_rate',
            [
            'type'     => Table::TYPE_INTEGER,
            'unsigned' => true,
            'nullable' => false,
            'default'  => 0,
            'comment'  => 'Facet min coverage rate',
            ]
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return void
     */
    public function updateAttributeFacetMinRateValues(): void
    {
        $attributes = $this->attributeCollection->create()
            ->join(
                [
                    'cea' => $this->moduleDataSetup->getTable('catalog_eav_attribute'),
                ],
                'main_table.attribute_id = cea.attribute_id',
                'cea.facet_min_coverage_rate'
            )
            ->addFieldToFilter('cea.facet_min_coverage_rate', ['neq' => 0]);
        foreach ($attributes as $attribute) {
            $attribute->setFacetMinCoverageRate('0');
        }

        $attributes->save();
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
