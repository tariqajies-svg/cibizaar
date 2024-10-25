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

namespace Magebit\Brands\ViewModel;

use Magebit\Brands\Api\BrandsManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Brand implements ArgumentInterface
{
    /**
     * @param BrandsManagementInterface $brandsManagement
     */
    public function __construct(
        private readonly BrandsManagementInterface $brandsManagement
    ) {
    }

    /**
     * Get Brand URL from product
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getUrlForProduct(ProductInterface $product): ?string
    {
        return $this->brandsManagement->getProductBrandUrl($product);
    }

    /**
     * Get Brand URL from brand option id
     *
     * @param string $optionId
     * @return string|null
     */
    public function getUrlByBrandOptionId(string $optionId): ?string
    {
        return $this->brandsManagement->getBrandUrl($optionId);
    }

    /**
     * Check if brand URL exists for product
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function hasBrandUrl(ProductInterface $product): bool
    {
        return !!$this->brandsManagement->getProductBrandUrl($product);
    }
}
