<?php
/**
 * This file is part of the Magebit_CurrencyRate package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_CurrencyRate
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\CurrencyRate\Setup\Patch\Data;

use Magento\Directory\Model\ResourceModel\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDHSRate implements DataPatchInterface
{
    /**
     * AddDHSRate constructor
     *
     * @param Currency $currency
     */
    public function __construct(
        private readonly Currency $currency
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
     * Add DHS -> AED rate to enable currency switcher
     * Both are the same currency, but FedEx for some reason is using DHS
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        $this->currency->saveRates([
            'DHS' => [
                'AED' => 1
            ]
        ]);
    }
}
