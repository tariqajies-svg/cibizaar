<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Sales\Cron;

use Magebit\Sales\Service\ReservedOrderManagement;

class ReservedOrderCancel
{
    /**
     * ReservedOrderCancel constructor
     *
     * @param ReservedOrderManagement $reservedOrderManagement
     */
    public function __construct(
        private readonly ReservedOrderManagement $reservedOrderManagement
    ) {
    }

    /**
     * Cancel orders that are past time limit
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void
    {
        $this->reservedOrderManagement->cancelOrders();
    }
}
