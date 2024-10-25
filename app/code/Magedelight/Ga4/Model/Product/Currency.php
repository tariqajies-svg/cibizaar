<?php
/**
 * Magedelight
 *
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Model\Product;

use Magento\Framework\Option\ArrayInterface;

class Currency implements ArrayInterface
{
    /**
     * OptionArray
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'base_currency',
                'label' => __('Base Currency')
            ],
            [
                'value' => 'store_currency',
                'label' => __('Store Currency')
            ]
        ];
    }
}
