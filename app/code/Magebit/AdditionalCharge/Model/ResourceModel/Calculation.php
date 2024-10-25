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

namespace Magebit\AdditionalCharge\Model\ResourceModel;

use Magento\Framework\DataObject;

class Calculation extends \Magento\Tax\Model\ResourceModel\Calculation
{
    /**
     * Overwriten getRateInfo to filter out handle charge tax rates
     *
     * @param DataObject $request
     * @param bool $allowCharges
     * @return array
     */
    public function getRateInfo($request, $allowCharges = false)
    {
        $rates = $this->_getRates($request);

        $filteredRates = [];

        foreach ($rates as $rate) {
            if (!$allowCharges && isset($rate['is_charge']) && $rate['is_charge']) {
                continue;
            }
            $filteredRates[] = $rate;
        }

        return [
            'process' => $this->getCalculationProcess($request, $filteredRates),
            'value' => $this->_calculateRate($filteredRates)
        ];
    }
}
