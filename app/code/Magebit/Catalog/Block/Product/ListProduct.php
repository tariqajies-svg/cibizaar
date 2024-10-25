<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ListProduct as ListProductParent;

class ListProduct extends ListProductParent
{
    /**
     * Retrieve list pagination toolbar HTML
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildBlock('toolbar')->setIsPager(true)->toHtml();
    }
}
