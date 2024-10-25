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

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

use function array_map as map;
use function array_merge as merge;
use function array_unique as unique;
use function array_values as values;

/**
 * Subcategory tree view model.
 *
 * This view model is useful when you need to get a child categories tree for specific parent category.
 * You can specify parent by using setCategoryId, otherwise it will return tree for current category.
 *
 * Caching is implemented for this view model (method: getIdentities). Example how to add caching in phtml:
 *  $block->setData('cache_tags', $subcategoryTreeViewModel->getIdentities());
 */
class SubcategoryTree implements ArgumentInterface, IdentityInterface
{
    /**
     * Cache tag to use instead of category tags  if more than 200 categories are rendered in the navigation.
     */
    public const CACHE_TAG = 'subcategory_tree';

    private array $cacheIdentities;
    private int|bool $requestedMaxLevel;
    private int|bool $requestedRootCategoryId;

    /**
     * The maximum number of category cache identities to return before using a single hyva_nav cache tag instead.
     *
     * @var int
     */
    private int $maxCategoryCacheTags;

    protected CollectionFactory $categoryCollectionFactory;
    protected CategoryFactory $categoryFactory;

    public function __construct(
        CollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        int $maxCategoryCacheTags = 200
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->maxCategoryCacheTags = $maxCategoryCacheTags;
    }

    /**
     * Get child categories with specified level grouped by parents
     *
     * @param CategoryInterface $category
     * @param int $levels
     * @return CategoryInterface[]
     * @throws LocalizedException
     */
    public function getDescendantsTree(CategoryInterface $category, int $levels = 2): array
    {
        $categories = $this->getDescendants($category, $levels);
        $categoriesTree = $this->arrayToTree($categories->toArray());

        return $this->arrayToObjects($this->processCacheIdentities($categoriesTree, $levels, (int)$category->getId()));
    }

    /**
     * Get child categories with specified level
     *
     * @param CategoryInterface $category
     * @param int $levels
     * @return Collection
     * @throws LocalizedException
     */
    public function getDescendants(CategoryInterface $category, int $levels = 2): Collection
    {
        if ($levels < 1) {
            $levels = 1;
        }

        return $this->categoryCollectionFactory->create()
            ->addIsActiveFilter()
            ->addAttributeToSelect([
                CategoryInterface::KEY_NAME,
                'image',
                'url_path'
            ])
            ->addPathsFilter($category->getPath() . '/')
            ->addLevelFilter($category->getLevel() + $levels);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities(): array
    {
        return isset($this->cacheIdentities) && $this->cacheIdentities && count($this->cacheIdentities) > $this->maxCategoryCacheTags
            ? [self::CACHE_TAG]
            : $this->cacheIdentities ?? [];
    }

    /**
     * @param array $tree
     * @param int $levels
     * @param int $rootCategoryId
     * @return array
     */
    protected function processCacheIdentities(array $tree, int $levels, int $rootCategoryId): array
    {
        if ($this->isNewMaxLevel($levels)
            && $this->isNewRootCategoryId($rootCategoryId)
            && !empty($tree)
        ) {
            $this->requestedMaxLevel = $levels;
            $this->requestedRootCategoryId = $rootCategoryId;
            $this->cacheIdentities = unique(merge(...values(map([$this, 'extractCacheIdentities'], $tree))));
        }
        return array_map([$this, 'removeCacheIdentities'], $tree);
    }

    /**
     * @param int $levels
     * @return bool
     */
    protected function isNewMaxLevel(int $levels): bool
    {
        return !isset($this->cacheIdentities) || ( // this is the first request
                $this->requestedMaxLevel !== false && // previous requests where not unlimited
                $levels > $this->requestedMaxLevel // this request has a higher limit than previous ones
            );
    }

    /**
     * @param int $rootCategoryId
     * @return bool
     */
    protected function isNewRootCategoryId(int $rootCategoryId): bool
    {
        return !isset($this->cacheIdentities) || ( // this is the first request
                $this->requestedRootCategoryId !== false && // previous requests where not unlimited
                $rootCategoryId != $this->requestedRootCategoryId // this request has a different id than previous ones
            );
    }

    /**
     * @param array $menuData
     * @return array
     */
    protected function extractCacheIdentities(array $menuData): array
    {
        $identities = $menuData['identities'] ?? [];
        return merge($identities, ...values(map([$this, 'extractCacheIdentities'], $menuData['children_tree'] ?? [])));
    }

    /**
     * @param array $menuData
     * @return array
     */
    protected function removeCacheIdentities(array $menuData): array
    {
        $menuData['children_tree'] = map([$this, 'removeCacheIdentities'], $menuData['children_tree'] ?? []);
        unset($menuData['identities']);

        return $menuData;
    }

    /**
     * Creates a tree from associative array based on 'parent_id' value
     *
     * @param array $array
     * @return array
     */
    protected function arrayToTree(array $array): array
    {
        $tree = [];
        foreach ($array as &$item) {
            if (isset($item['parent_id']) && $parent = $item['parent_id']) {
                $array[$parent]['children_tree'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }

        if (count($tree) === 1 && isset(reset($tree)['children_tree'])) {
            return reset($tree)['children_tree'];
        }

        return $tree;
    }

    /**
     * @param array $array
     * @return CategoryInterface[]
     */
    protected function arrayToObjects(array $array): array
    {
        foreach ($array as &$item) {
            $item = $this->categoryFactory->create()->setData($item);
            $childrenTree = $item->getData('children_tree');
            if ($childrenTree && is_array($childrenTree)) {
                $item->setData('children_tree', $this->arrayToObjects($childrenTree));
            }
        }

        return $array;
    }
}
