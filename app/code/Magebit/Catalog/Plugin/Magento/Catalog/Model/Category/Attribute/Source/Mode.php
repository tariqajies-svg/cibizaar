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

namespace Magebit\Catalog\Plugin\Magento\Catalog\Model\Category\Attribute\Source;

use Magento\Catalog\Model\Category\Attribute\Source\Mode as ModeSubject;

class Mode
{
    /**
     * Category display modes
     */
    public const DM_SUBCATEGORIES_AND_PRODUCTS = 'SUBCATEGORIES_AND_PRODUCTS';

    /**
     * Add new category display mode.
     *
     * @param ModeSubject $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllOptions(ModeSubject $subject, array $result): array
    {
        $result[] = ['value' => self::DM_SUBCATEGORIES_AND_PRODUCTS, 'label' => __('Subcategories and products')];
        return $result;
    }
}
