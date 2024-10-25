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

use Hyva\Theme\Service\Navigation as HyvaNavigation;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Navigation extends HyvaNavigation
{
    protected const BRAND_ATTRIBUTE_CODE = 'manufacturer';

    protected Attribute $eavAttribute;

    public function __construct(
        CategoryHelper                  $catalogCategory,
        StateDependentCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface           $storeManager,
        NodeFactory                     $nodeFactory,
        TreeFactory                     $treeFactory,
        Resolver                        $layerResolver,
        Attribute                       $eavAttribute
    ) {
        parent::__construct($catalogCategory, $categoryCollectionFactory, $storeManager, $nodeFactory, $treeFactory, $layerResolver);

        $this->eavAttribute = $eavAttribute;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCustomMenuTree($rootId, $maxLevel, $topLevel): Node
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->getCustomCategoryTree($storeId, $rootId, $maxLevel, $topLevel);
        $currentCategory = $this->getCurrentCategory();
        $menu = $this->getMenuTemplate();
        $mapping = [(is_array($rootId) ? array_pop($rootId) : $rootId) => $menu];

        foreach ($collection as $category) {
            $categoryParentId = $category->getParentId();
            if (!isset($mapping[$categoryParentId])) {
                $parentIds = $category->getParentIds();
                foreach ($parentIds as $parentId) {
                    if (isset($mapping[$parentId])) {
                        $categoryParentId = $parentId;
                    }
                }
            }

            $parentCategoryNode = $mapping[$categoryParentId];
            $categoryNode = new Node(
                $this->getCategoryAsArray(
                    $category,
                    $currentCategory,
                    $category->getParentId() == $categoryParentId
                ),
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
     * @param array|int $rootId
     * @param int $maxLevel
     * @param int $topLevel
     * @return Node
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCustomFooterMenuTree(array|int $rootId, int $maxLevel, int $topLevel): Node
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        $collection = $this->getCustomCategoryTree($storeId, $rootId, $maxLevel, $topLevel);
        $collection->addAttributeToSelect('include_in_footer');
        $collection->addAttributeToFilter('include_in_footer', '1');
        $currentCategory = $this->getCurrentCategory();
        $menu = $this->getMenuTemplate();
        $rootCategoryId = is_array($rootId) ? array_pop($rootId) : $rootId;
        $mapping = [$rootCategoryId => $menu];

        foreach ($collection as $category) {
            $parentCategoryNode = $mapping[$rootCategoryId];
            $categoryNode = new Node(
                $this->getCategoryAsArray(
                    $category,
                    $currentCategory,
                    true
                ),
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
     * @return Node
     */
    protected function getMenuTemplate(): Node
    {
        return $this->nodeFactory->create([
            'data' => [],
            'idField' => 'root',
            'tree' => $this->treeFactory->create(),
        ]);
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
        int $storeId,
        array|int $rootId,
        int $maxLevel = 0,
        int $topLevel = 0
    ): CategoryCollection {
        /** @var CategoryCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name']);
        $collection->addFieldToFilter(
            'path',
            ['like' => '1/' . (is_array($rootId) ? implode('/', $rootId) : $rootId) . '/%']
        );
        $collection->addAttributeToFilter('include_in_menu', '1');
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

        $collection->getSelect()->joinLeft(
            ['ea' => 'eav_attribute'],
            'ea.attribute_code = "' . self::BRAND_ATTRIBUTE_CODE . '" AND ea.entity_type_id = 4',
            ''
        )->joinLeft(
            ['ccev' => 'catalog_category_entity_varchar'],
            'ccev.attribute_id = ' . $this->getAttributeIdByCode('brand') . ' AND ccev.entity_id = e.entity_id',
            ''
        )->joinLeft(
            ['eao' => 'eav_attribute_option'],
            'eao.attribute_id = ea.attribute_id AND eao.option_id = ccev.value',
            ''
        )->joinLeft(
            ['eaov' => 'eav_attribute_option_value'],
            'eao.option_id = eaov.option_id AND eaov.store_id = ' . $storeId,
            'eaov.value as brand'
        );

        return $collection;
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
            'brand' => $category->getData('brand') ?: '',
            'url' => $this->catalogCategory->getCategoryUrl($category),
            'has_child' => $category->hasChildren(),
            'parent_id' => $category->getParentId(),
            'position' => $category->getData('position'),
            'identities' => $category->getIdentities()
        ];
    }

    /**
     * @param string $code
     * @return int
     */
    protected function getAttributeIdByCode(string $code): int
    {
        return (int)$this->eavAttribute->getIdByCode(Category::ENTITY, $code);
    }
}
