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

namespace Magebit\Brands\Api;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface BrandsManagementInterface
{
    public const PRODUCT_ATTRIBUTE_CODE = 'manufacturer';
    public const CATEGORY_ATTRIBUTE_CODE =  'brand';

    /**
     * Get Brand category by brand code
     *
     * @param string $optionId
     * @return CategoryInterface|null
     */
    public function getBrandCategory(string $optionId): ?CategoryInterface;

    /**
     * Get Brand url by brand code
     *
     * @param string $optionId
     * @return string|null
     */
    public function getBrandUrl(string $optionId): ?string;

    /**
     * Get brand url from product
     *
     * @param ProductInterface $product
     * @return string|null
     */
    public function getProductBrandUrl(ProductInterface $product): ?string;
}
