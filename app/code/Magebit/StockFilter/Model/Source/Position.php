<?php
/**
 * This file is part of the Magebit_StockFilter package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_StockFilter
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\StockFilter\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Position implements OptionSourceInterface
{
    public const POSITION_TOP = 'top';
    public const POSITION_BOTTOM = 'bottom';
    public const POSITION_AFTER_CATEGORY = 'after_category';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::POSITION_TOP,
                'label' => __('At the top')
            ],
            [
                'value' => self::POSITION_BOTTOM,
                'label' => __('At the bottom')
            ],
            [
                'value' => self::POSITION_AFTER_CATEGORY,
                'label' => __('After the category filter')
            ]
        ];
    }
}
