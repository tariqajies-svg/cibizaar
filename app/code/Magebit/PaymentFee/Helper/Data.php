<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    protected const CONFIG_XML_PAYMENT_CCAVENUE_PAYMENT_FEE_PERCENTAGE = 'payment/ccavenue/payment_fee_percentage';
    protected const CONFIG_XML_PAYMENT_NGENIUSONLINE_PAYMENT_FEE_PERCENTAGE = 'payment/ngeniusonline/payment_fee_percentage';

    /**
     * @return float
     */
    public function getCCAvenuePaymentFeePercentage(): float
    {
        return (float)$this->scopeConfig->getValue(self::CONFIG_XML_PAYMENT_CCAVENUE_PAYMENT_FEE_PERCENTAGE);
    }

    /**
     * @return float
     */
    public function getNgeniusOnlinePaymentFeePercentage(): float
    {
        return (float)$this->scopeConfig->getValue(self::CONFIG_XML_PAYMENT_NGENIUSONLINE_PAYMENT_FEE_PERCENTAGE);
    }
}
