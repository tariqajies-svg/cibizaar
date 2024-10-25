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

namespace Magebit\Sales\Setup\Patch\Data;

use Exception;
use Magebit\Sales\Api\Data\ReservedOrderStatusInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\StatusFactory;

class UpdatePendingReservationFront implements DataPatchInterface
{
    /**
     * UpdatePendingReservationFront constructor
     *
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        private readonly StatusFactory $statusFactory,
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            CreateReservedOrderStatuses::class
        ];
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
     * Update Pending Reservation status
     *
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $status = $this->statusFactory->create();
        $status->setStatus(ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS);
        $status->setLabel('Pending Reservation');
        $status->assignState(ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS, false, true);
    }
}
