<?php
/**
 *
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

namespace Magedelight\Ga4\Model\Config\Source;

class Summary implements \Magento\Framework\Option\ArrayInterface
{
    public const CHECKOUT_SUBTOTAL = 'subtotal';
    public const CHECKOUT_TOTAL = 'grandtotal';

    /**
     * OptionArray
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CHECKOUT_TOTAL,
                'label' => __('Grandtotal')
            ],
            ['value' => self::CHECKOUT_SUBTOTAL,
                'label' => __('Subtotal')
            ]
        ];
    }
}
