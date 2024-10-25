<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Sales\Observer\Catalog;

use Magebit\Catalog\ViewModel\Stock;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class ProductIsSalableAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param GetProductSalableQtyInterface $salableQty
     * @param Stock $stock
     */
    public function __construct(
        protected readonly GetProductSalableQtyInterface $salableQty,
        protected readonly Stock $stock
    ) {
    }

    /**
     * Salable qty additional check. If salable qty is 0, then mark product as not salable.
     *
     * @param Observer $observer
     * @return ProductIsSalableAfter
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(
        Observer $observer
    ): ProductIsSalableAfter {
        /** @var Product $product */
        $product = $observer->getProduct();
        /** @var DataObject $salable */
        $salable = $observer->getSalable();

        $product->setSkipSaleableCheck(true);

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $atLeastOneChildAvailable = false;
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);

            foreach ($childProducts as $childProduct) {
                $stockItem = $this->stock->getStockItem($childProduct);
                $salableQty = $this->salableQty->execute($childProduct->getSku(), $stockItem->getStockId());

                if ($salableQty > 0) {
                    $atLeastOneChildAvailable = true;
                    break;
                }
            }

            if (!$atLeastOneChildAvailable) {
                $salable->setIsSalable(false);
                $product->setIsSaleable(false);
            }
        } else {
            $stockItem = $this->stock->getStockItem($product);
            $salableQty = $this->salableQty->execute($product->getSku(), $stockItem->getStockId());

            if ($salableQty <= 0) {
                $salable->setIsSalable(false);
                $product->setIsSaleable(false);
            }
        }

        $product->setSkipSaleableCheck(false);

        return $this;
    }
}
