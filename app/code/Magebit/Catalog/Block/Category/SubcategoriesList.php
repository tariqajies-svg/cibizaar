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

namespace Magebit\Catalog\Block\Category;

use Magento\Framework\Registry;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @method int setCategoryId()
 */
class SubcategoriesList extends View
{
    protected ?CategoryInterface $category;

    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        Category $categoryHelper,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);

        $this->categoryRepository = $categoryRepository;
        $this->category = null;
    }

    /**
     * Returns category ID from data or current category
     *
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        return (int)$this->getData('category_id') ?: (int)$this->getRequest()->getParam('id', null);
    }

    /**
     * Returns category based on category ID
     *
     * @return CategoryInterface|null
     */
    public function getCategory(): ?CategoryInterface
    {
        if (!$this->category && $categoryId = $this->getCategoryId()) {
            try {
                $this->category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                $this->category = null;
            }
        }

        return $this->category;
    }

    protected function _toHtml()
    {
        if ($this->isMixedMode()) {
            return parent::_toHtml();
        }
        return '';
    }
}
