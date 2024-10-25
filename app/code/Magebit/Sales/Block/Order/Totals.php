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
namespace Magebit\Sales\Block\Order;

use Magento\Sales\Block\Order\Totals as TotalsDefault;

class Totals extends TotalsDefault
{
    /**
     * Remove grand total with base currency
     *
     * @param TotalsDefault $subject
     * @param array $result
     * @return mixed
     */
    public function afterGetTotals(TotalsDefault $subject, array $result)
    {
        unset($result['base_grandtotal']);

        return $result;
    }
}
