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

namespace Magebit\AdditionalCharge\Service;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Calculation as CalculationResource;

class ChargeRateManagement
{
    /**
     * ChargeRateManagement constructor
     *
     * @param TaxRateRepositoryInterface $rateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CalculationResource $calculationResource
     * @param Calculation $calculation
     * @param AddressInterfaceFactory $addressInterfaceFactory
     */
    public function __construct(
        private readonly TaxRateRepositoryInterface $rateRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CalculationResource $calculationResource,
        private readonly Calculation $calculation,
        private readonly AddressInterfaceFactory $addressInterfaceFactory
    ) {
    }

    /**
     * Get amount of extra charge to be added for shipping
     *
     * @param RateRequest $request
     * @return float
     * @throws InputException
     */
    public function getAdditionalCharge(RateRequest $request): float
    {
        $rates = $this->getAdditionalChargeRates();
        $applicableRates = $this->calculationResource->getRateInfo($this->transformRequest($request), true);

        foreach ($applicableRates['process'] as $process) {
            foreach ($process['rates'] as $rate) {
                foreach ($rates as $chargeRate) {
                    if ($chargeRate->getId() === $rate['rate_id']) {
                        return $request->getBaseSubtotalWithDiscountInclTax() * ($chargeRate->getRate()/100);
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Get all rates that are marked for additional charge
     *
     * @return TaxRateInterface[]
     * @throws InputException
     */
    private function getAdditionalChargeRates(): array
    {
        $rates = $this->rateRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('is_charge', 1)
                ->create()
        );

        return $rates->getItems();
    }

    /**
     * Transform shipping request into something we can use for tax calculation
     *
     * @param RateRequest $request
     * @return DataObject
     */
    private function transformRequest(RateRequest $request): DataObject
    {
        $address = $this->addressInterfaceFactory->create()
            ->setCity($request->getDestCity())
            ->setRegionCode($request->getDestRegionCode())
            ->setRegionId($request->getDestRegionId())
            ->setCountryId($request->getDestCountryId())
            ->setPostcode($request->getDestPostcode());
        return $this->calculation->getRateRequest(
            shippingAddress: $address
        );
    }
}
