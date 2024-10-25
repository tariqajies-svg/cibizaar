<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\Block\Product\Listing;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\View;

class ProductQuantity extends View
{
    /**
     * Retrieve current product model
     *
     * @return null|Product
     */
    public function getProduct(): ?Product
    {
        return $this->getData('product');
    }
}
