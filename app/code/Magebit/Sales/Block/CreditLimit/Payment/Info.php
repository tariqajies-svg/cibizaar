<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Sales\Block\CreditLimit\Payment;

class Info extends \Aheadworks\CreditLimit\Block\Payment\Info
{
    /**
     * Disable AheadWorks PO number block. It is replaced with \Magebit\Sales\Block\Adminhtml\Order\View\PoNumber
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        return '';
    }
}
