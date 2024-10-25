<?php
namespace SkyExLabel\Slabel\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    public function beforeSetLayout(OrderView $subject)
    {
        $subject->addButton(
            'order_custom_button',
            [
                'label' => __('Custom Button'),
                'class' => __('custom-button'),
                'id' => 'order-view-custom-button',
                'onclick' => 'setLocation(\'' . $subject->getUrl('module/controller/action') . '\')'
            ]
        );
    }
}