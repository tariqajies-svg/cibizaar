<?php
/**
 * This file is part of the Magebit_AdditionalCharge package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_AdditionalCharge
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AdditionalCharge\Plugin;

use Magebit\AdditionalCharge\Service\ChargeRateManagement;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class ShippingPlugin
{
    private const BLACKLISTED_CARRIERS = [
        'freeshipping',
        'amstorepick'
    ];

    /**
     * ShippingPlugin constructor
     *
     * @param ChargeRateManagement $chargeRateManagement
     */
    public function __construct(
        private readonly ChargeRateManagement $chargeRateManagement
    ) {
    }

    /**
     * Add extra charge for shipping methods if necessary
     *
     * @param Shipping $subject
     * @param Shipping $result
     * @param RateRequest $request
     * @return Shipping
     * @throws InputException
     */
    public function afterCollectRates(Shipping $subject, Shipping $result, RateRequest $request): Shipping
    {
        $additionalCharge = $this->chargeRateManagement->getAdditionalCharge($request);
        foreach ($result->getResult()->getAllRates() as $rate) {
            if (!in_array($rate->getCarrier(), self::BLACKLISTED_CARRIERS)) {
                $rate->setPrice($rate->getPrice() + $additionalCharge);
            }
        }

        return $result;
    }
}
