<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\Invoice\Totals as TotalsAlias;
use Magento\Tax\Block\Sales\Order\Tax as TaxDefault;

class Tax extends TaxDefault
{

    /**
     * Changing Tax order (after subtotal)
     *
     * @return $this
     */
    public function initTotals()
    {
        /** @var $parent TotalsAlias */
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->_order->getStore();
        $allowTax = $this->_source->getTaxAmount() > 0 || $this->_config->displaySalesZeroTax($store);
        $grandTotal = (double)$this->_source->getGrandTotal();
        if (!$grandTotal || $allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store)) {
            // TI-340
            $this->_addTax('subtotal');
        }

        $this->_initSubtotal();
        $this->_initShipping();
        $this->_initDiscount();
        $this->_initGrandTotal();
        return $this;
    }
}
