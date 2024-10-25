<?php
/**
 * This file is part of the Magebit_Breadcrumbs package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Breadcrumbs\Rewrite\Magento\Catalog\Block;

use Magento\Catalog\Block\Breadcrumbs as BreadcrumbsBlock;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Breadcrumbs as BreadcrumbsHtmlBlock;

class Breadcrumbs extends BreadcrumbsBlock
{
    /**
     * @param Context $context
     * @param Data $catalogData
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $catalogData,
        protected readonly Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $catalogData, $data);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getRootCategoryId(): int
    {
        return (int)$this->_storeManager->getStore()->getRootCategoryId();
    }

    /**
     * Always display the deepest category for product breadcrumbs.
     *
     * @param Product $product
     * @param BreadcrumbsHtmlBlock $breadcrumbsHtmlBlock
     * @param array $title
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function addProductBreadcrumbs(Product $product, BreadcrumbsHtmlBlock $breadcrumbsHtmlBlock, array &$title): void
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = clone $product->getCategoryCollection();
        $categoryCollection->clear()
            ->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)
            ->addAttributeToFilter('path', ['like' => "1/" . $this->getRootCategoryId() . "/%"])
            ->setPageSize(1);

        /** @var array $breadcrumbCategories */
        $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
        usort($breadcrumbCategories, function ($a, $b) {
            return strcmp($a->getLevel(), $b->getLevel());
        });

        /** @var Category $category */
        foreach ($breadcrumbCategories as $category) {
            $breadcrumbsHtmlBlock->addCrumb('category' . $category->getId(), [
                'label' => $category->getName(),
                'title' => $category->getName(),
                'link' => $category->getUrl()
            ]);
            $title[] = $category->getName();
        }

        $breadcrumbsHtmlBlock->addCrumb('product', [
            'label' => $product->getName(),
            'title' => $product->getName(),
            'link' => ''
        ]);
    }

    /**
     * Overriding default catalog product breadcrumbs. Always display the deepest category for product breadcrumbs.
     *
     * @return Breadcrumbs
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout(): self
    {
        /** @var BreadcrumbsHtmlBlock $BreadcrumbsHtmlBlock */
        if ($BreadcrumbsHtmlBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $BreadcrumbsHtmlBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            /** @var Product $product */
            $product = $this->registry->registry('current_product');
            $title = [];

            if ($product) {
                $this->addProductBreadcrumbs($product, $BreadcrumbsHtmlBlock, $title);
            } else {
                $path = $this->_catalogData->getBreadcrumbPath();

                foreach ($path as $name => $breadcrumb) {
                    $BreadcrumbsHtmlBlock->addCrumb($name, $breadcrumb);
                    $title[] = $breadcrumb['label'];
                }
            }

            $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));
        }

        return $this;
    }
}
