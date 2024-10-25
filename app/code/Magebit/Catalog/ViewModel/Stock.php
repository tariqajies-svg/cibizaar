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

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class Stock implements ArgumentInterface
{
    public const LOW_STOCK_PERCENT = 0.25;
    public const MEDIUM_STOCK_PERCENT = 0.75;

    /** @var StockItemInterface */
    private StockItemInterface $stockItem;

    /**
     * @param StockItemRepository $stockItemRepository
     * @param StockItemCriteriaInterface $stockItemCriteria
     * @param GetProductSalableQtyInterface $salableQty
     */
    public function __construct(
        private readonly StockItemRepository $stockItemRepository,
        private readonly StockItemCriteriaInterface $stockItemCriteria,
        private readonly GetProductSalableQtyInterface $salableQty,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param Product $product
     * @return float|int
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function getStockLoad(Product $product): float|int
    {
        $stockItem = $this->getStockItem($product);
        $qty = $stockItem->getQty();

        if ($product->getTypeId() === Product\Type::TYPE_SIMPLE || $product->getTypeId() === Product\Type::TYPE_VIRTUAL) {
            $qty = $this->salableQty->execute($product->getSku(), $stockItem->getStockId());
        }

        if ($qty >= $stockItem->getMinSaleQty()) {
            return $qty / 100;
        } elseif ($qty < $stockItem->getMinSaleQty()) {
            return 0;
        }
    }

    /**
     * @param Product $product
     * @return StockItemInterface
     * @throws NoSuchEntityException
     */
    public function getStockItem(Product $product): StockItemInterface
    {
        if (!isset($this->stockItem)) {
            $criteria = $this->stockItemCriteria;
            $criteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $product->getId());
            $stockItems = $this->stockItemRepository->getList($criteria)->getItems();
            $this->stockItem = reset($stockItems);
        }

        return $this->stockItem;
    }

    /**
     * Get full product stock data
     *
     * Return everything on load to allow for alpinejs
     * component to have this data available
     *
     * @param Product $product
     * @return array
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStockData(Product $product): array
    {
        $stockData = [];

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProducts as $child) {
                $childStockItem = $this->getStockItem($child);
                $stockData[$child->getId()] = [
                    'isSalable' => $child->getIsSalable(),
                    'stockLoad' => $this->getStockLoad($child),
                    'qty' => $this->salableQty->execute($child->getSku(), $childStockItem->getStockId())
                ];
            }
        }

        $stockData[$product->getId()] = $this->getProductStock($product);

        return $stockData;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProductStock(Product $product): array
    {
        $stockItem = $this->getStockItem($product);
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $qty = $stockItem->getQty();
        } else {
            $qty = $this->salableQty->execute($product->getSku(), $stockItem->getStockId());
        }
        return [
            'isSalable' => $product->getIsSalable(),
            'stockLoad' => $this->getStockLoad($product),
            'qty' => $qty
        ];
    }

    /**
     * Get Ready to dispatch text
     *
     * @param Product $product
     * @return string
     */
    public function getDispatchMessage(Product $product): string
    {
        $stockItem = $this->getStockItem($product);
        return $stockItem->getBackorders()
            ? $this->scopeConfig->getValue('catalog/frontend/dispatch_backorder_text') ?? ''
            : $this->scopeConfig->getValue('catalog/frontend/dispatch_text') ?? '';
    }
}
