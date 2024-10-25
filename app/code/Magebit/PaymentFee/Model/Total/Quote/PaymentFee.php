<?php

/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Model\Total\Quote;

use Infibeam\Ccavenue\Model\Ccavenue;
use Magebit\PaymentFee\Helper\Data;
use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use NetworkInternational\NGenius\Gateway\Config\Config as NgeniusOnline;

class PaymentFee extends AbstractTotal
{
    public const TOTAL_CODE = 'payment_fee';

    public const ENTITY_FIELD_PAYMENT_FEE = 'payment_fee';
    public const ENTITY_FIELD_BASE_PAYMENT_FEE = 'base_payment_fee';

    /**
     * @param Data $helper
     */
    public function __construct(
        protected readonly Data $helper
    ) {
        $this->setCode(self::TOTAL_CODE);
    }

    /**
     * Collect "Payment Fee" for Quote
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): self {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $this->clearValues($quote, $total);

        $baseFeeAmount = $this->calculateBasePaymentFeeAmount($quote, $total);
        $quoteFeeAmount = $this->convertFromBaseToQuoteCurrency($quote, $baseFeeAmount);

        $total->setData(self::ENTITY_FIELD_PAYMENT_FEE, $quoteFeeAmount);
        $total->setData(self::ENTITY_FIELD_BASE_PAYMENT_FEE, $baseFeeAmount);

        $total->setTotalAmount(self::ENTITY_FIELD_PAYMENT_FEE, $quoteFeeAmount);
        $total->setBaseTotalAmount(self::ENTITY_FIELD_PAYMENT_FEE, $baseFeeAmount);

        $quote->setData(self::ENTITY_FIELD_PAYMENT_FEE, $quoteFeeAmount);
        $quote->setData(self::ENTITY_FIELD_BASE_PAYMENT_FEE, $baseFeeAmount);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total): array
    {
        $result = [];
        $amount = $total->getData(self::ENTITY_FIELD_PAYMENT_FEE) ?: $quote->getData(self::ENTITY_FIELD_PAYMENT_FEE);

        $label = $this->getLabel();

        // Hardcoded: show percentage for "CCAvenue" only
        if ($quote->getPayment()->getMethod() === Ccavenue::PAYMENT_CCA_CODE) {
            $label = __('Payment Fee (%1%)', $this->helper->getCCAvenuePaymentFeePercentage());
        }

        // Hardcoded: "Payment fee" calculates only for "Ngenius Online" payment method
        if ($quote->getPayment()->getMethod() === NgeniusOnline::CODE) {
            $label = __('Payment Fee (%1%)', $this->helper->getNgeniusOnlinePaymentFeePercentage());
        }

        if ($amount) {
            $result = [
                'code' => $this->getCode(),
                'title' => $label,
                'value' => $amount
            ];
        }

        return $result;
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Payment Fee');
    }

    /**
     * Convert from base to quote currency
     *
     * @param float $amount
     * @param Quote $quote
     * @return float
     */
    protected function convertFromBaseToQuoteCurrency(Quote $quote, float $amount): float
    {
        return $amount * $quote->getBaseToQuoteRate();
    }

    /**
     * Calculate payment fee amount
     *
     * @param Quote $quote
     * @param Total $total
     * @return float
     */
    protected function calculateBasePaymentFeeAmount(Quote $quote, Total $total): float
    {
        // Hardcoded: "Payment fee" calculates only for "CCAvenue" payment method
        $cCAvenuePaymentFeePercentage = $this->helper->getCCAvenuePaymentFeePercentage();

        if ($cCAvenuePaymentFeePercentage > 0 && $quote->getPayment()->getMethod() === Ccavenue::PAYMENT_CCA_CODE) {
            $baseTotals = array_sum($total->getAllBaseTotalAmounts());
            return ($cCAvenuePaymentFeePercentage / 100) * $baseTotals;
        }

        // Hardcoded: "Payment fee" calculates only for "Ngenius Online" payment method
        $ngeniusOnlinePaymentFeePercentage = $this->helper->getNgeniusOnlinePaymentFeePercentage();

        if ($ngeniusOnlinePaymentFeePercentage > 0 && $quote->getPayment()->getMethod() === NgeniusOnline::CODE) {
            $baseTotals = array_sum($total->getAllBaseTotalAmounts());
            return ($ngeniusOnlinePaymentFeePercentage / 100) * $baseTotals;
        }

        return 0;
    }

    /**
     * Clear payment fee related total values
     *
     * @param Quote $quote
     * @param Total $total
     * @return void
     */
    protected function clearValues(Quote $quote, Total $total): void
    {
        $amount = 0;

        $total->setData(self::ENTITY_FIELD_PAYMENT_FEE, $amount);
        $total->setData(self::ENTITY_FIELD_BASE_PAYMENT_FEE, $amount);

        $total->setTotalAmount(self::ENTITY_FIELD_PAYMENT_FEE, $amount);
        $total->setBaseTotalAmount(self::ENTITY_FIELD_PAYMENT_FEE, $amount);

        $quote->setData(self::ENTITY_FIELD_PAYMENT_FEE, $amount);
        $quote->setData(self::ENTITY_FIELD_BASE_PAYMENT_FEE, $amount);
    }
}
