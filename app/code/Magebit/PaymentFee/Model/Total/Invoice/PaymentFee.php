<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Model\Total\Invoice;

use Infibeam\Ccavenue\Model\Ccavenue;
use Magebit\PaymentFee\Model\Total\Quote\PaymentFee as QuotePaymentFee;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use NetworkInternational\NGenius\Gateway\Config\Config as NgeniusOnline;

class PaymentFee extends AbstractTotal
{
    /**
     * Collect "Payment Fee" for Invoice
     *
     * @param Invoice $invoice
     * @return PaymentFee
     */
    public function collect(Invoice $invoice): self
    {
        /** @var $order OrderInterface */
        $order = $invoice->getOrder();

        $paymentMethod = $order->getPayment()->getMethod();

        // Hardcoded: "Payment fee" calculates only for "CCAvenue" and "NgeniusOnline" payment methods
        if ($paymentMethod !== Ccavenue::PAYMENT_CCA_CODE
            && $paymentMethod !== NgeniusOnline::CODE) {
            return $this;
        }

        $paymentFee = $order->getData(QuotePaymentFee::ENTITY_FIELD_PAYMENT_FEE);
        $basePaymentFee = $order->getData(QuotePaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE);

        if ($paymentFee && $basePaymentFee) {
            $invoice->setData(QuotePaymentFee::ENTITY_FIELD_PAYMENT_FEE, $paymentFee);
            $invoice->setData(QuotePaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE, $basePaymentFee);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $paymentFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePaymentFee);
        }

        return $this;
    }
}
