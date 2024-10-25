<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

use Magento\Framework\Stdlib\ArrayManager;

use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Date;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

/**
 * Data provider for back_in_stock (attribute) field
 */
class BackInStock extends AbstractModifier
{
    private const BACK_IN_STOCK_FIELD = 'back_in_stock';

    /** @var array */
    private array $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        private readonly LocatorInterface $locator,
        private readonly ArrayManager $arrayManager,
    ) {
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        $fieldCode = self::BACK_IN_STOCK_FIELD;

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $highlightsData = $model->getBackInStock();

        if ($highlightsData) {
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/' . self::BACK_IN_STOCK_FIELD;
            $data = $this->arrayManager->set($path, $data, $highlightsData);
        }
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $this->meta = $meta;
        $this->initBackInStockFields();
        return $this->meta;
    }

    /**
     * Customize back_in_stock field
     *
     * @return $this
     */
    protected function initBackInStockFields(): static
    {
        $backInStockPath = $this->arrayManager->findPath(
            self::BACK_IN_STOCK_FIELD,
            $this->meta,
            null,
            'children'
        );

        if ($backInStockPath) {
            $this->meta = $this->arrayManager->merge(
                $backInStockPath,
                $this->meta,
                $this->initBackInStockFieldStructure($backInStockPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($backInStockPath, 0, -3)
                . '/' . self::BACK_IN_STOCK_FIELD,
                $this->meta,
                $this->arrayManager->get($backInStockPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($backInStockPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Get back_in_stock dynamic rows structure
     *
     * @param string $backInStockPath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initBackInStockFieldStructure(string $backInStockPath): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Back In Stock'),
                        'required' => false,
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' => $this->arrayManager->get(
                            $backInStockPath . '/arguments/data/config/sortOrder',
                            $this->meta
                        )
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'qty' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Qty'),
                                        'dataScope' => 'qty',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],

                        'date' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' =>  'date',
                                        'componentType' => Field::NAME,
                                        'dataType' => Date::NAME,
                                        'label' => __('Date'),
                                        'dataScope' => 'date',
                                        'require' => '1',
                                    ],
                                ],
                            ],
                        ],

                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
