<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Model\Total\Creditmemo;

use Infibeam\Ccavenue\Model\Ccavenue;
use Magebit\PaymentFee\Model\Total\Quote\PaymentFee as QuotePaymentFee;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use NetworkInternational\NGenius\Gateway\Config\Config as NgeniusOnline;

class PaymentFee extends AbstractTotal
{
    /**
     * Collect "Payment Fee" for Creditmemo
     *
     * @param Creditmemo $creditmemo
     * @return PaymentFee
     */
    public function collect(Creditmemo $creditmemo): self
    {
        /** @var $order OrderInterface */
        $order = $creditmemo->getOrder();

        $paymentMethod = $order->getPayment()->getMethod();

        // Hardcoded: "Payment fee" calculates only for "CCAvenue" and "NgeniusOnline" payment methods
        if ($paymentMethod !== Ccavenue::PAYMENT_CCA_CODE
            && $paymentMethod !== NgeniusOnline::CODE) {
            return $this;
        }

        $paymentFee = $order->getData(QuotePaymentFee::ENTITY_FIELD_PAYMENT_FEE);
        $basePaymentFee = $order->getData(QuotePaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE);

        if ($paymentFee && $basePaymentFee) {
            $creditmemo->setData(QuotePaymentFee::ENTITY_FIELD_PAYMENT_FEE, $paymentFee);
            $creditmemo->setData(QuotePaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE, $basePaymentFee);
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $paymentFee);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basePaymentFee);
        }

        return $this;
    }
}
