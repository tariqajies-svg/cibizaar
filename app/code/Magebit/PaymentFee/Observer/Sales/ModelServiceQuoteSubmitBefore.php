<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Observer\Sales;

use Magebit\PaymentFee\Model\Total\Quote\PaymentFee;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * Copy "Payment Fee" from "Quote" to "Order"
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var $order OrderInterface * */
        $order = $observer->getData('order');
        /** @var $quote CartInterface */
        $quote = $observer->getData('quote');

        if ($quote->getData(PaymentFee::ENTITY_FIELD_PAYMENT_FEE)
            && $quote->getData(PaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE)
        ) {
            $order->setData(
                PaymentFee::ENTITY_FIELD_PAYMENT_FEE,
                $quote->getData(PaymentFee::ENTITY_FIELD_PAYMENT_FEE)
            );
            $order->setData(
                PaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE,
                $quote->getData(PaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE)
            );
        }
    }
}
