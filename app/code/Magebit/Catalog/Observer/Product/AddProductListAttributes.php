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

namespace Magebit\Catalog\Observer\Product;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class AddProductListAttributes implements ObserverInterface
{
    /**
     * @param CollectionFactory $categoryCollection
     */
    public function __construct(
        private readonly CollectionFactory $categoryCollection
    ) {
    }

    /**
     * Add product list attributes to current product
     * from the deepest category
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getProduct();
        $categoryIds = $product->getCategoryIds();
        $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('*')
            ->addIdFilter($categoryIds);
        $currentCategory = null;

        foreach ($categories->getItems() as $category) {
            if ($category->getProductListAttributes()) {
                if (!$currentCategory || $category->getLevel() > $currentCategory->getLevel()) {
                    $currentCategory = $category;
                }
            }
        }

        if ($currentCategory) {
            $product->setProductListAttributes(explode(',', $currentCategory->getProductListAttributes()));
        }
    }
}
