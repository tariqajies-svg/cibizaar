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

namespace Magebit\Catalog\Plugin\Magento\Catalog\Block\Category;

use Magebit\Catalog\Plugin\Magento\Catalog\Model\Category\Attribute\Source\Mode;
use Magento\Catalog\Block\Category\View as ViewSubject;

class View
{
    /**
     * Check if category display mode is "Static Block and Products" or "Subcategories and products"
     *
     * @param ViewSubject $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMixedMode(ViewSubject $subject, bool $result): bool
    {
        return $result ?: $subject->getCurrentCategory()->getDisplayMode() == Mode::DM_SUBCATEGORIES_AND_PRODUCTS;
    }
}
