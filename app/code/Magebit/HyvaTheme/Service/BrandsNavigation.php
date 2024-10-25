<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\Service;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionFactory as EmptyCollectionFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteVirtualCategory\Model\ResourceModel\VirtualCategory\CollectionFactory;

class BrandsNavigation extends Navigation
{
    public const CATEGORY_DATA_KEY_CHILDREN_CATEGORY_IDS = 'children_category_ids';

    public function __construct(
        CategoryHelper $catalogCategory,
        StateDependentCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        Resolver $layerResolver,
        Attribute $eavAttribute,
        protected readonly EmptyCollectionFactory $emptyCollectionFactory,
        protected readonly CollectionFactory $virtualCategoryCollectionFactory,
        protected readonly ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct(
            $catalogCategory,
            $categoryCollectionFactory,
            $storeManager,
            $nodeFactory,
            $treeFactory,
            $layerResolver,
            $eavAttribute
        );
    }

    /**
     * @param $rootId
     * @param $maxLevel
     * @param $topLevel
     * @param array|null $brandCategories - array with all brands categories
     *
     * @return Node
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomMenuTree($rootId, $maxLevel, $topLevel, array &$brandCategories = null): Node
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        // Get Brand Collection with children IDs
        // Children categories are set
        $brandCollection = $this->collectChildrenCategoryIds($storeId, $brandCategories);

        // Load all children via one request
        $allChildrenCategoriesCollection = $this->loadAllChildrenCategoriesCollection(
            $storeId,
            $brandCollection,
            $maxLevel,
            $topLevel
        );

        // Prepare menu tree variables
        $currentCategory = $this->getCurrentCategory();
        $menu = $this->getMenuTemplate();
        $mapping = [(is_array($rootId) ? array_pop($rootId) : $rootId) => $menu];

        $allSimpleChildrenCategoriesArray = [];

        // Purpose of this foreach to collect the $topLevel categories with parent brand Ids
        // Firstly we are going through all brands and their children
        foreach ($brandCollection as $brand) {
            $parentId = $brand->getParentId();
            if (!isset($mapping[$parentId])) {
                continue;
            }
            $parentCategory = $mapping[$parentId];

            $childrenCategoryIds = $brand->getData(self::CATEGORY_DATA_KEY_CHILDREN_CATEGORY_IDS);

            // Secondly we are foreach'ing all $topLevel children categories for brand and adding them to the tree
            // We are foreach'ing only $topLevel categories, because they can be under multiple brands in the tree
            foreach ($childrenCategoryIds as $childrenCategoryId) {
                /** @var Category $children */
                $children = $allChildrenCategoriesCollection->getItemById($childrenCategoryId);
                if (!$children || !$children->getId()) {
                    continue;
                }

                // Skip children categories which are not $topLevel and add them into new separate array
                // We will add them to the tree later, because these children has only one parent ID (in the tree)
                if ((int)$children->getLevel() !== $topLevel) {
                    if (isset($allSimpleChildrenCategoriesArray[$children->getId()])) {
                        $children = $allSimpleChildrenCategoriesArray[$children->getId()];
                    }
                    $parentIdsTemp = $children->getData('parent_ids') ?: [];
                    $parentIdsTemp[] = $brand->getId();
                    $children->setData('parent_ids', array_unique($parentIdsTemp));

                    $allSimpleChildrenCategoriesArray[$children->getId()] = $children;
                    continue;
                }

                if (isset($mapping[$children->getId()])) {
                    $childCategoryNode = $mapping[$children->getId()];
                } else {
                    $categoryData = $this->getCategoryAsArray($children, $currentCategory, false);
                    $childCategoryNode = new Node(
                        $categoryData,
                        'id',
                        $parentCategory->getTree(),
                        $parentCategory
                    );
                }

                $parentIdsTemp = $childCategoryNode->getData('parent_ids');
                $parentIdsTemp[] = $brand->getId();
                $childCategoryNode->setData('parent_ids', array_unique($parentIdsTemp));

                if (isset($brandCategories[$brand->getId()])) {
                    $brandCategories[$brand->getId()]['has_child'] = true;
                }

                $parentCategory->addChild($childCategoryNode);
                $mapping[$children->getId()] = $childCategoryNode; //add node in stack
            }
        }

        // Collect other levels (not $topLevel) and add them into the tree
        // These levels has only one parent Id in the tree
        foreach ($allSimpleChildrenCategoriesArray as $category) {
            if ((int)$category->getLevel() === $topLevel) {
                continue;
            }

            $categoryParentId = $category->getParentId();
            if (!isset($mapping[$categoryParentId])) {
                $parentIds = $category->getParentIds();
                foreach ($parentIds as $parentId) {
                    if (isset($mapping[$parentId])) {
                        $categoryParentId = $parentId;
                    }
                }
            }

            // Skip category if parent ID is missing. This happens when parent is disabled.
            if (!isset($mapping[$categoryParentId])) {
                continue;
            }

            $parentCategoryNode = $mapping[$categoryParentId];

            $categoryArray = $this->getCategoryAsArray(
                $category,
                $currentCategory,
                $category->getParentId() == $categoryParentId
            );
            $categoryArray['parent_ids'] = $category->getData('parent_ids');

            $categoryNode = new Node(
                $categoryArray,
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);
            $mapping[$category->getId()] = $categoryNode; //add node in stack
        }

        return $menu;
    }

    /**
     * Convert category to array
     *
     * @param Category $category
     * @param Category $currentCategory
     * @param bool $isParentActive
     * @return array
     */
    public function getCategoryAsArray($category, $currentCategory, $isParentActive): array
    {
        $categoryId = $category->getId();

        return [
            'name' => $category->getName(),
            'id' => $categoryId,
            'url' => $this->catalogCategory->getCategoryUrl($category),
            'url_key' => $category->getUrlKey(),
            'has_child' => $category->hasChildren(),
            'brands' => [],
            'parent_ids' => [],
            'parent_id' => $category->getParentId(),
            'position' => $category->getData('position'),
            'identities' => $category->getIdentities(),
        ];
    }

    /**
     * @param $storeId
     * @param $brandCategories
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function collectChildrenCategoryIds($storeId, &$brandCategories): AbstractDb
    {
        // Get brand collection
        $brandCollection = $this->virtualCategoryCollectionFactory->create();
        $brandCollection->setStoreId($storeId);
        $brandCollection->addFieldToFilter('entity_id', ['in' => array_keys($brandCategories)]);
        $brandCollection->addAttributeToSelect([
            'is_active',
            'is_virtual_category',
            'virtual_category_root',
            'generate_root_category_subtree',
            'is_anchor',
            'url_key'
        ]);
        $brandCollection->addIsActiveFilter();

        // Collect all category IDs for products in each brand
        /** @var Category $brandCategory */
        foreach ($brandCollection as &$brandCategory) {
            // Not virtual categories should not have children
            if (!$this->isVirtual($brandCategory)) {
                $brandCategory->setData(self::CATEGORY_DATA_KEY_CHILDREN_CATEGORY_IDS, []);
                continue;
            }
            $categoriesArray = $this->getCategoryProducts($brandCategory)->getColumnValues('category_ids');
            $categoriesUniqueArray = [];
            // Get all products from brand -> get all category IDs for products
            array_walk_recursive($categoriesArray, function ($a) use (&$categoriesUniqueArray) {
                $categoriesUniqueArray[] = $a;
            });
            // Get unique array of children categories for brand
            $categoriesUniqueArray = array_unique($categoriesUniqueArray);

            // Save children categories into brand
            $brandCategory->setData(self::CATEGORY_DATA_KEY_CHILDREN_CATEGORY_IDS, $categoriesUniqueArray);

            // Removing brands which don't have subcategories
            if (!count($categoriesUniqueArray)) {
                unset($brandCategories[$brandCategory->getId()]);
            }
        }

        return $brandCollection;
    }

    /**
     * @param CategoryInterface $category
     * @return ProductCollection
     */
    private function getCategoryProducts(CategoryInterface $category): ProductCollection
    {
        $productCollection = $this->productCollectionFactory->create();
        $queryFilter = $category->getVirtualRule()->getCategorySearchQuery($category->getId());
        $productCollection->addQueryFilter($queryFilter);
        return $productCollection;
    }

    /**
     * @param CategoryInterface|Category $category
     * @return bool
     */
    private function isVirtual(CategoryInterface $category): bool
    {
        return $category->getIsVirtualCategory() && $category->getVirtualRule();
    }

    /**
     * @param $storeId
     * @param $brandCollection
     * @param $maxLevel
     * @param $topLevel
     * @return CategoryCollection
     * @throws LocalizedException
     */
    protected function loadAllChildrenCategoriesCollection(
        $storeId,
        $brandCollection,
        $maxLevel,
        $topLevel
    ): CategoryCollection {
        $allChildrenCategoryIds = [];
        foreach ($brandCollection as $brand) {
            array_push($allChildrenCategoryIds, ...$brand->getData(self::CATEGORY_DATA_KEY_CHILDREN_CATEGORY_IDS));
        }
        $allChildrenCategoryIds = array_unique($allChildrenCategoryIds);
        return $this->getCustomCategoryTree($storeId, $allChildrenCategoryIds, $maxLevel, $topLevel);
    }

    /**
     * Get Category Tree
     *
     * @param int $storeId
     * @param array|int $rootId
     * @param int $maxLevel
     * @param int $topLevel
     * @return CategoryCollection
     * @throws LocalizedException
     */
    public function getCustomCategoryTree(
        int       $storeId,
        array|int $rootId,
        int       $maxLevel = 0,
        int       $topLevel = 0
    ): CategoryCollection {
        /** @var CategoryCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_key']);
        $collection->addFieldToFilter('entity_id', ['in' => $rootId]);
        $collection->addAttributeToFilter('include_in_menu', 1);
        $collection->addIsActiveFilter();
        if ($maxLevel > 0) {
            $collection->addLevelFilter($maxLevel);
        }
        if ($topLevel > 0) {
            $collection->addFieldToFilter('level', ['gteq' => $topLevel]);
        }
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);

        return $collection;
    }
}
