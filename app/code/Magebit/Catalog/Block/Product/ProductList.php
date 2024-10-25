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

namespace Magebit\Catalog\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Hyva\Theme\ViewModel\ProductList as ProductListDefault;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product\LinkFactory as ProductLinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Review\Model\ResourceModel\Review\Summary as ReviewSummaryResource;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Framework\EntityManager\MetadataPool as EntityMetadataPool;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ProductList extends ProductListDefault
{
    // @codingStandardsIgnoreStart
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductLinkCollectionFactory $productLinkCollectionFactory
     * @param ProductLinkFactory $productLinkFactory
     * @param CatalogConfig $catalogConfig
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CategoryFactory $categoryFactory
     * @param ReviewSummaryResource $reviewSummaryResource
     * @param bool $isIncludingReviewSummary
     * @param int $maxCrosssellItemCount
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrderBuilder $sortOrderBuilder,
        ProductCollectionFactory $productCollectionFactory,
        ProductLinkCollectionFactory $productLinkCollectionFactory,
        ProductLinkFactory $productLinkFactory,
        CatalogConfig $catalogConfig,
        CollectionProcessorInterface $collectionProcessor,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        ReviewSummaryResource $reviewSummaryResource,
        ProductVisibility $productVisibility,
        EntityMetadataPool $entityMetadataPool,
        ScopeConfigInterface $scopeConfig,
        bool $isIncludingReviewSummary = true,
        private readonly int $maxCrosssellItemCount = 10
    ) {
        parent::__construct(
            $searchCriteriaBuilder,
            $filterBuilder,
            $sortOrderBuilder,
            $productCollectionFactory,
            $productLinkCollectionFactory,
            $productLinkFactory,
            $catalogConfig,
            $collectionProcessor,
            $categoryFactory,
            $categoryRepository,
            $reviewSummaryResource,
            $productVisibility,
            $entityMetadataPool,
            $scopeConfig,            
            $isIncludingReviewSummary,
            $maxCrosssellItemCount
        );
    }
    // @codingStandardsIgnoreEnd
}
