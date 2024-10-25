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

namespace Magebit\AdditionalCharge\Setup\Patch\Data;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Calculation\Rate;

class SetDefaultRateAsCharge implements DataPatchInterface
{
    private const TAX_RATE_CODES = [
        '[Additional Charge] All UAE',
        '[Additional Charge] UAE Jebel Ali 0%'
    ];

    /**
     * SetDefaultRateAsCharge constructor
     *
     * @param TaxRateRepositoryInterface $rateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly TaxRateRepositoryInterface $rateRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Set defined tax rates as additional charge as configurator can't process this custom field
     *
     * @return void
     * @throws InputException
     */
    public function apply(): void
    {
        $rates = $this->rateRepository->getList(
            $this->searchCriteriaBuilder
            ->addFilter(Rate::KEY_CODE, self::TAX_RATE_CODES, 'in')
            ->create()
        );

        foreach ($rates->getItems() as $rate) {
            $rate->setIsCharge(true);
            $this->rateRepository->save($rate);
        }
    }
}
