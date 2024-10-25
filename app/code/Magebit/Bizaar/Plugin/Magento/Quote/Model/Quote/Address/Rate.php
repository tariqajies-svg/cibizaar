<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Bizaar\Plugin\Magento\Quote\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address\Rate as RateAddress;
use Magento\Quote\Model\Quote\Address\Rate as RateSubject;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;
use Magento\Quote\Model\Quote\Address\RateResult\Error;

class Rate
{
    /**
     * @param RateSubject $subject
     * @param RateSubject $result
     * @param AbstractResult $rate
     *
     * @return RateSubject
     */
    public function afterImportShippingRate(
        RateSubject $subject,
        RateAddress $result,
        AbstractResult $rate
    ): RateAddress {
        if ($rate instanceof Error) {
            $result->setMethod(
                $rate->getMethod()
            )->setMethodTitle(
                $rate->getMethodTitle()
            )->setMethodDescription(
                $rate->getMethodDescription()
            );
        }

        return $result;
    }
}
