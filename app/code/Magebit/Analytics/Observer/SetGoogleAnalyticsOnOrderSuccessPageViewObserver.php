<?php
/**
 * This file is part of the Magebit_Analytics package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Dunkin
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */
declare(strict_types=1);

namespace Magebit\Analytics\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Google Analytics module observer
 */
class SetGoogleAnalyticsOnOrderSuccessPageViewObserver implements ObserverInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(
        LayoutInterface $layout
    ) {
        $this->layout = $layout;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = $this->layout->getBlock('google_gtag_analytics');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
