<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Aheadworks
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Aheadworks\ViewModel\Ca\Company;

use Magento\Directory\Block\Data;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Form implements ArgumentInterface
{
    /**
     * @param Data $directoryData
     */
    public function __construct(
        private readonly Data $directoryData
    ) {
    }

    /**
     * @return Collection
     */
    public function getCountryCollection(): Collection
    {
        return $this->directoryData->getCountryCollection();
    }

    /**
     * @return array[]
     */
    public function getCompanyDescriptionOptions(): array
    {
        return [
            [
                'value' => __('System Integrators'),
                'label' => __('System Integrators'),
            ],
            [
                'value' => __('Corporate'),
                'label' => __('Corporate'),
            ],
            [
                'value' => __('Startup'),
                'label' => __('Startup'),
            ],
            [
                'value' => __('Reseller'),
                'label' => __('Reseller'),
            ],
            [
                'value' => __('Wholesalers'),
                'label' => __('Wholesalers'),
            ],
            [
                'value' => __('Other'),
                'label' => __('Other'),
            ],
        ];
    }
}
