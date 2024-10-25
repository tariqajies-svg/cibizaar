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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magedelight\Ga4\Helper\Data;
use Magento\Checkout\Model\Session;
use Magedelight\Ga4\Model\EventTrigger;

class AddtoCartAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param Session $checkoutSession
     * @param Data $datahelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(Session $checkoutSession, Data $datahelper, EventTrigger $eventTrigger)
    {
        $this->checkoutSession = $checkoutSession;
        $this->datahelper = $datahelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $cookieConcent = $this->datahelper->getCookie();
        $accessible = 1;
        if ($this->datahelper->getGDPRStatus()) {
            if (isset($cookieConcent)) {
                $accessible = 1;
            } else {
                $accessible = 0;
            }
        }
        if ($this->datahelper->isGTMStatus() && $accessible == 1 && $this->datahelper->GTMEventConfigured('add_to_cart')) {
            $product = $observer->getEvent()->getProduct();
            $item = $observer->getEvent()->getData('quote_item');
            $item_price = $this->getItemPrice($item);
            if ($product->getTypeId()=='grouped') {
                $qty = $item->getQtyToAdd();
            } else {
                $qty = $product->getQty();
            }
            $event = "add_to_cart";
            $variant = "";
            
            if ($this->datahelper->getVariantStatus()) {
                $option = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $ptype = $item->getProductType();
                $variant = $this->datahelper->getProductOption($option, $ptype);
            }

            $this->checkoutSession
            ->setAddToCartData(
                $this->eventTrigger
                ->cartEventTrigger(
                    $event,
                    $qty,
                    $product,
                    $item_price,
                    $variant
                )
            );
        }

        return $this;
    }

    private function getItemPrice($item){
        
        $currencyOption = $this->datahelper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return number_format($item->getPrice(), 2, '.', '');
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $this->datahelper->getFormatedPrice($item->getPrice());
        }
    }
}
