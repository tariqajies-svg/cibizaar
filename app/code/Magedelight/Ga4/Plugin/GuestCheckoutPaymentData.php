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

namespace Magedelight\Ga4\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface as Salesorder;
use Magedelight\Ga4\Helper\Data as mdhelper;
use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Checkout\Model\GuestPaymentInformationManagement as PaymentInfo;
use Magedelight\Ga4\Model\EventTrigger;

class GuestCheckoutPaymentData
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $salesorder;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutsession;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * @var checkoutSession
     */
    protected $CheckoutSession;

    /**
     * Construct
     *
     * @param Salesorder $salesorder
     * @param mdhelper $mdhelper
     * @param checkoutSession $checkoutsession
     * @param EventTrigger $eventTrigger
     */
    public function __construct(
        Salesorder $salesorder,
        mdhelper $mdhelper,
        checkoutSession $checkoutsession,
        EventTrigger $eventTrigger
    ) {
        $this->mdhelper = $mdhelper;
        $this->CheckoutSession = $checkoutsession;
        $this->salesorder = $salesorder;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * AfterSavePaymentInformationAndPlaceOrder
     *
     * @param PaymentInfo $subject
     * @param result $result
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInfo $subject,
        $result
    ) {
        if ($this->mdhelper->isGTMStatus() && $this->mdhelper->GTMEventConfigured('add_payment_info')) {
            $salesData = $this->CheckoutSession->getLastRealOrder();
            if (!$salesData->getId()) {
                try {
                    $id = $result;
                    $salesData = $this->salesorder->get($id);
                } catch (\Exception $ex) {
                    return $result;
                }
            }

            $getCheckoutData = $salesData->getPayment()->getAdditionalInformation();
            if ($getCheckoutData && isset($getCheckoutData['method_title'])) {
                $methodName = $getCheckoutData['method_title'];
                $eventName = "add_payment_info";
                $this->CheckoutSession->setCheckoutOptionsData(
                    $this->eventTrigger->paymentCheckoutEventTrigger(
                        '3',
                        $methodName,
                        $eventName
                    )
                );
            }
        }
        return $result;
    }
}
