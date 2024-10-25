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

use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Model\Calculation\Rate\Converter;

class ConverterPlugin
{
    /**
     * Set is_charge data to object before saving
     *
     * @param Converter $subject
     * @param TaxRateInterface $result
     * @param array $formData
     * @return TaxRateInterface
     */
    public function afterPopulateTaxRateData(Converter $subject, TaxRateInterface $result, array $formData): TaxRateInterface
    {
        if (isset($formData['is_charge'])) {
            $result->setIsCharge($formData['is_charge']);
        } else {
            $result->setIsCharge(false);
        }

        return $result;
    }

    /**
     * Get saved is_charge value for form
     *
     * @param Converter $subject
     * @param array $result
     * @param TaxRateInterface $taxRate
     * @return array
     */
    public function afterCreateArrayFromServiceObject(Converter $subject, array $result, TaxRateInterface $taxRate): array
    {
        $result['is_charge'] = $taxRate->getIsCharge();

        return $result;
    }
}
