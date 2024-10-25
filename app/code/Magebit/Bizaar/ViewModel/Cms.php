<?php
/**
 * This file is part of the Magebit_Configurator package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\Bizaar\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * CMS view model for getting product
 */
class Cms implements ArgumentInterface
{
    /**
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory
    ) {
    }

    /**
     * Load category model by url key
     *
     * @param string $sku
     * @param int $store
     * @return Product
     * @throws LocalizedException
     */
    public function getProduct(string $sku, int $store = 0): Product
    {
        try {
            /** @var Product $product */
            $product = $this->productCollectionFactory->create()
                ->addAttributeToFilter('sku', $sku)
                ->setStoreId($store)
                ->getFirstItem();
        } catch (LocalizedException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $product;
    }
}
