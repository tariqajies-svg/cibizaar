<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Plugin\Magento\Catalog\Block\ProductList;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Update toolbar count for the category list view
 */
class UpdateToolbarCount
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var AreProductsSalableInterface
     */
    private $areProductsSalable;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param AreProductsSalableInterface $areProductsSalable
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        AreProductsSalableInterface $areProductsSalable,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->areProductsSalable = $areProductsSalable;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Update toolbar count if store is in single source mode
     *
     * @param Toolbar $subject
     * @param int $result
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetTotalNum(Toolbar $subject, int $result): int
    {
        if ($this->stockConfiguration->isShowOutOfStock()) {
            try {
                $defaultScopeId = $this->storeManager->getWebsite()->getCode();
                $stock_id = (int) $this->stockRegistry->getStock($defaultScopeId)->getStockId();
                $skus = [];
                $items = $subject->getCollection()->getItems();
                array_walk(
                    $items,
                    function ($item) use (&$skus) {
                        array_push($skus, $item->getSku());
                    }
                );
                $salableProducts = $this->areProductsSalable->execute($skus, $stock_id);
                if ($salableProducts) {
                    $result = count($salableProducts);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $result;
    }
}
