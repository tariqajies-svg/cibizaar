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

namespace Magebit\Sales\Setup\Patch\Data;

use Exception;
use Magebit\Sales\Api\Data\ReservedOrderStatusInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class CreateReservedOrderStatuses implements DataPatchInterface
{
    private const NEW_STATUSES = [
        [
            'status' => ReservedOrderStatusInterface::RESERVED_STATUS,
            'label' => 'Reserved',
            'state' => 'new',
            'is_default' => true,
            'visible_on_front' => true
        ],
        [
            'status' => ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS,
            'label' => 'Pending Reservation',
            'state' => 'new',
            'is_default' => false
        ]
    ];

    /**
     * CreateReservedOrderStatuses constructor
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        private readonly StatusFactory $statusFactory,
        private readonly StatusResourceFactory $statusResourceFactory
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
     * Create new order statuses and assign one as default new status
     *
     * @return void
     * @throws Exception
     */
    public function apply(): void
    {
        $statusResource = $this->statusResourceFactory->create();

        foreach (self::NEW_STATUSES as $statusData) {
            try {
                $status = $this->statusFactory->create()
                    ->setData($statusData);

                $statusResource->save($status);
                $status->assignState($statusData['state'], $statusData['is_default'], $statusData['visible_on_front'] ?? false);
            } catch (AlreadyExistsException) {
                continue;
            }
        }
    }
}
