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

namespace Magebit\Sales\Block\Email\Payment;

use Magento\Framework\View\Element\Template;

class PoNumber extends Template
{
    protected $_template = 'Magebit_Sales::email/payment/po_number.phtml';

    /**
     * @param string $poNumber
     * @return $this
     */
    public function setPoNumber(string $poNumber): self
    {
        $this->setData('po_number', $poNumber);
        return $this;
    }

    /**
     * @return string
     */
    public function getPoNumber(): string
    {
        return $this->getData('po_number');
    }
}
