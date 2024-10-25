<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCreditLimit package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCreditLimit
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCreditLimit\ViewModel;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Pricing implements ArgumentInterface
{
    /** @var Data */
    private Data $helperData;

    /**
     * Pricing constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Return formatted price
     *
     * @param float $price
     * @return string
     */
    public function getFormattedPrice(float $price): string
    {
        return $this->helperData->currency($price, true, false);
    }
}
