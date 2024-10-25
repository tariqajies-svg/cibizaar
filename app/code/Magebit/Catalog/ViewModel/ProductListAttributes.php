<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductListAttributes implements ArgumentInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $categoryCollection
     * @param AttributeCollectionFactory $attributeCollection
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CollectionFactory $categoryCollection,
        private readonly AttributeCollectionFactory $attributeCollection
    ) {
    }

    /**
     * @param Product $product
     * @return array|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductAttributes(Product $product): ?array
    {
        if ($attributeIds = $product->getProductListAttributes()) {
            $attributes = $this->attributeCollection->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('main_table.attribute_id', ['in' => $attributeIds]);

            $productAttributeList = null;
            foreach ($attributes->getItems() as $attribute) {
                if ($value = $product->getData($attribute->getAttributeCode())) {
                    $attrValue = $this->getAttributeValue($attribute, $value);
                    $productAttributeList[] = [
                        'label' => $attribute->getFrontendLabel(),
                        'value' => is_array($attrValue) ? implode(', ', $attrValue) : $attrValue
                    ];
                }
            }

            return $productAttributeList;
        }

        return null;
    }

    /**
     * @param DataObject $attribute
     * @param $value
     * @return mixed
     */
    private function getAttributeValue(DataObject $attribute, $value): mixed
    {
        $inputType = $attribute->getFrontendInput();

        if ($inputType == 'select' || $inputType == 'boolean') {
            $value = $attribute->getSource()->getOptionText($value);
        } elseif ($inputType == 'multiselect') {
            $multivalue = explode(',', $value);
            if (count($multivalue) > 1) {
                $value = [];
                foreach ($attribute->getSource()->getAllOptions() as $option) {
                    if (in_array($option['value'], $multivalue)) {
                        $value[] = $option['label'];
                    }
                }
            } else {
                $value = $attribute->getSource()->getOptionText($value);
            }
        } else {
            return $value;
        }

        return $value;
    }
}
