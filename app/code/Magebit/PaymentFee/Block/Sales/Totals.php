<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Block\Sales;

use Infibeam\Ccavenue\Model\Ccavenue;
use Magebit\PaymentFee\Helper\Data;
use Magebit\PaymentFee\Model\Total\Quote\PaymentFee;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Totals as TotalsParent;
use NetworkInternational\NGenius\Gateway\Config\Config as NgeniusOnline;

class Totals extends TotalsParent
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param Factory $factory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly Factory $factory,
        protected readonly Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
    }

    /**
     * Add "Payment Fee" total
     *
     * @return $this
     */
    public function initTotals(): self
    {
        $source = $this->getSource();

        if (!$source
            || !$source->getData(PaymentFee::ENTITY_FIELD_PAYMENT_FEE)
            || !$source->getData(PaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE)
        ) {
            return $this;
        }

        $paymentMethod = $source->getPayment()->getMethod();

        // Hardcoded: "Payment fee" shows percents only for "CCAvenue" and "NgeniusOnline" payment methods
        $label = __('Payment Fee');

        if ($paymentMethod === Ccavenue::PAYMENT_CCA_CODE) {
            $label = __('Payment Fee (%1%)', $this->helper->getCCAvenuePaymentFeePercentage());
        } elseif ($paymentMethod === NgeniusOnline::CODE) {
            $label = __('Payment Fee (%1%)', $this->helper->getNgeniusOnlinePaymentFeePercentage());
        }

        /** @var TotalsParent $parentBlock */
        $parentBlock = $this->getParentBlock();
        $parentBlock->addTotalBefore(
            $this->factory->create(
                [
                    'code' => PaymentFee::TOTAL_CODE,
                    'label' => $label,
                    'value' => $source->getData(PaymentFee::ENTITY_FIELD_PAYMENT_FEE),
                    'base_value' => $source->getData(PaymentFee::ENTITY_FIELD_BASE_PAYMENT_FEE)
                ]
            ),
            'grand_total'
        );

        return $this;
    }
}
