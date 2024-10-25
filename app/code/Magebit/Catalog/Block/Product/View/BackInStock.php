<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Block\Product\View;

use Hyva\Theme\ViewModel\CurrentProduct;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magebit\Catalog\Setup\Patch\Data\CreateProductBackInStockAttribute as BackInStockAttribute;

class BackInStock extends Template
{
    /**
     * BackInStock constructor
     *
     * @param Context $context
     * @param CurrentProduct $currentProduct
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly CurrentProduct $currentProduct,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get current product
     *
     * @return ProductInterface|null
     */
    public function getProduct(): ?ProductInterface
    {
        return $this->hasData('product')
            ? $this->getData('product')
            : $this->currentProduct->get();
    }

    /**
     * @return bool
     */
    public function isConfigurableProduct(): bool
    {
        return $this->getProduct()->getTypeId() === Configurable::TYPE_CODE;
    }

    /**
     * Return first child product ID if configurable
     * or current simple product id
     *
     * @return int|string
     */
    public function getProductId(): int|string
    {
        $product = $this->getProduct();

        if ($this->isConfigurableProduct()) {
            $reversedIds = array_reverse($product->getTypeInstance()->getChildrenIds($product->getId())[0]);

            return array_pop($reversedIds);
        }

        return $product->getId();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBackInStock(): array
    {
        $product = $this->getProduct();

        if ($this->isConfigurableProduct()) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            $storeId = $this->storeManager->getStore()->getId();
            $result = [];

            foreach ($children as $child) {
                $childId = $child->getId();
                $value = $child->getResource()->getAttributeRawValue(
                    $childId,
                    BackInStockAttribute::ATTRIBUTE_CODE,
                    $storeId
                );

                $result[$childId] = $value ? json_decode($value) : null;
            }

            return $result;
        }

        return [$product->getId() => $product->getBackInStock()];
    }
}
