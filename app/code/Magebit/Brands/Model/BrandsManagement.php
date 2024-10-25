<?php
/**
 * This file is part of the Magebit_Brands package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Brands
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Brands\Model;

use Magebit\Brands\Api\BrandsManagementInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class BrandsManagement implements BrandsManagementInterface
{
    /**
     * @param CategoryListInterface $categoryListRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly CategoryListInterface $categoryListRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Get Brand category by brand option ID
     *
     * @param string $optionId
     * @return CategoryInterface|null
     */
    public function getBrandCategory(string $optionId): ?CategoryInterface
    {
        $categories = $this->categoryListRepository->getList(
            $this->searchCriteriaBuilder
            ->addFilter(self::CATEGORY_ATTRIBUTE_CODE, $optionId)
            ->create()
        )->getItems();

        return empty($categories) ? null : current($categories);
    }

    /**
     * Get Brand url by brand option ID
     *
     * @param string $optionId
     * @return string|null
     */
    public function getBrandUrl(string $optionId): ?string
    {
        if ($category = $this->getBrandCategory($optionId)) {
            return $category->getUrl();
        }

        return null;
    }

    /**
     * Get brand url from product
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getProductBrandUrl(ProductInterface $product): ?string
    {
        if ($brand = $product->getCustomAttribute(self::PRODUCT_ATTRIBUTE_CODE)) {
            return $this->getBrandUrl($brand->getValue());
        }

        return null;
    }
}
