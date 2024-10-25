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

namespace Magedelight\Ga4\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Ga4\Helper\Data;
use Magento\Customer\Model\Session;
use Magedelight\Ga4\Model\EventTrigger;

class AddToWishList implements ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;
    
    /**
     * Construct
     *
     * @param Session $customerSession
     * @param Data $datahelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(Session $customerSession, Data $datahelper, EventTrigger $eventTrigger)
    {
        $this->customerSession = $customerSession;
        $this->datahelper = $datahelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->datahelper->isGTMStatus() && $this->datahelper->GTMEventConfigured('add_to_wishlist')) {
            $product = $observer->getEvent()->getProduct();
            $event = "add_to_wishlist";
            $this->customerSession->setAddToWishListData($this->eventTrigger->itemAddedEventTrigger($event, $product));
        }

        return $this;
    }
}
