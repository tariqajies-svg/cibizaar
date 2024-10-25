<?php
/**
 * This file is part of the Magebit_StockFilter package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_StockFilter
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\StockFilter\Plugin;

use Magebit\StockFilter\Model\Layer\Filter\Stock as StockFilter;
use Magebit\StockFilter\Model\Source\Position;
use Magento\Catalog\Model\Layer;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Smile\ElasticsuiteCatalog\Model\Layer\FilterList\Interceptor;
use Smile\ElasticsuiteVirtualCategory\Model\Layer\Filter\Category;

class FilterList
{
    private const CONFIG_ENABLED_XML_PATH   = 'stockfilter/settings/enabled';
    private const CONFIG_POSITION_XML_PATH  = 'stockfilter/settings/position';

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $outOfStockEnabled = $this->scopeConfig->isSetFlag(
            Configuration::XML_PATH_DISPLAY_PRODUCT_STOCK_STATUS,
            ScopeInterface::SCOPE_STORE
        );

        $extensionEnabled = $this->scopeConfig->isSetFlag(
            self::CONFIG_ENABLED_XML_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return $outOfStockEnabled && $extensionEnabled;
    }

    /**
     * @param Interceptor $filterList
     * @param Layer $layer
     * @return array
     */
    public function beforeGetFilters(
        Interceptor $filterList,
        Layer $layer
    ): array {
        $this->layer = $layer;

        return [$layer];
    }

    /**
     * @param Interceptor $filterList
     * @param array $filters
     * @return array
     */
    public function afterGetFilters(
        Interceptor $filterList,
        array $filters
    ): array {
        if ($this->isEnabled()) {
            $position = $this->getFilterPosition();
            $stockFilter = $this->getStockFilter();
            switch ($position) {
                case Position::POSITION_BOTTOM:
                    $filters[] = $stockFilter;
                    break;
                case Position::POSITION_TOP:
                    array_unshift($filters, $stockFilter);
                    break;
                case Position::POSITION_AFTER_CATEGORY:
                    $processed = [];
                    $stockFilterAdded = false;
                    foreach ($filters as $key => $value) {
                        $processed[] = $value;
                        if ($value instanceof Category) {
                            $processed[] = $stockFilter;
                            $stockFilterAdded = true;
                        }
                    }
                    $filters = $processed;
                    if (!$stockFilterAdded) {
                        array_unshift($filters, $stockFilter);
                    }
                    break;
            }
        }
        return $filters;
    }

    /**
     * @return StockFilter
     */
    public function getStockFilter(): StockFilter
    {
        return $this->objectManager->create(
            StockFilter::class,
            ['layer' => $this->layer]
        );
    }

    /**
     * @return string
     */
    public function getFilterPosition(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_POSITION_XML_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
