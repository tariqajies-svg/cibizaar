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

namespace Magebit\Sales\Observer\Payment;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class AssignPoNumber extends AbstractDataAssignObserver
{
    /**
     * Assigning PO number to the payment method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $dataObject = $this->readDataArgument($observer);

        $poNumber = $dataObject->getData('po_number');
        if (!$poNumber) {
            return;
        }

        $paymentModel = $this->readPaymentModelArgument($observer);
        $paymentModel->setPoNumber($poNumber);
    }
}
