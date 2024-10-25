<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Locale\CurrencyInterface;

class CurrencySymbol implements ArgumentInterface
{
    /**
     * @param CurrencyInterface $currency
     */
    public function __construct(private readonly CurrencyInterface $currency)
    {
    }

    /**
     * Retrieve currency name by code
     *
     * @param string $code
     * @return string
     */
    public function getCurrencySymbol(string $code): string
    {
        return $this->currency->getCurrency($code)->getSymbol() ?: '';
    }
}
