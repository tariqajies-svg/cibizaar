<?php

/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@magedelight.com>
 *
 * @category  Magedelight
 * @package   Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.magedelight.com/)
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author    Magedelight <info@magedelight.com>
 */
 
namespace Magedelight\Ga4\Model\Config\Source;

class Event implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $_groupCollectionFactory;
    
    public function __construct(\Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory)
    {
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'add_payment_info',
                'label' => __('add_payment_info')
            ],
            [
                'value' => 'add_shipping_info',
                'label' => __('add_shipping_info')
            ],
            [
                'value' => 'add_to_cart',
                'label' => __('add_to_cart')
            ],
            [
                'value' => 'add_to_wishlist',
                'label' => __('add_to_wishlist')
            ],
            [
                'value' => 'add_to_compare',
                'label' => __('add_to_compare')
            ],
            [
                'value' => 'begin_checkout',
                'label' => __('begin_checkout')
            ],
            [
                'value' => 'purchase',
                'label' => __('purchase')
            ],
            [
                'value' => 'remove_from_cart',
                'label' => __('remove_from_cart')
            ],
            [
                'value' => 'select_item',
                'label' => __('select_item')
            ],
            [
                'value' => 'view_cart',
                'label' => __('view_cart')
            ],
            [
                'value' => 'view_item',
                'label' => __('view_item')
            ],
            [
                'value' => 'view_item_list',
                'label' => __('view_item_list')
            ],
            [
                'value' => 'refund',
                'label' => __('refund')
            ],
            [
                'value' => 'view_promotion',
                'label' => __('view_promotion')
            ],
            [
                'value' => 'select_promotion',
                'label' => __('select_promotion')
            ]
        ];
    }
}
