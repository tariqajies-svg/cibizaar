<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\Sales\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

class PaymentMethods implements OptionSourceInterface
{
    /**
     * PaymentMethods constructor
     *
     * @param Data $paymentHelper
     */
    public function __construct(
        private readonly Data $paymentHelper
    ) {
    }

    /**
     * Get all payment methods as options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = $this->paymentHelper->getPaymentMethods();

        $options = [];

        foreach ($methods as $code => $method) {
            $options[] = [
                'value' => $code,
                'label' => $method['title'] ?? $code
            ];
        }

        return $options;
    }
}
