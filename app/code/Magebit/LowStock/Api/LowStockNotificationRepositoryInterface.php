<?php
/**
 * This file is part of the Magebit_LowStock package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_LowStock
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\LowStock\Api;

use Magebit\LowStock\Api\Data\LowStockNotificationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface LowStockNotificationRepositoryInterface
{
    /**
     * Get Low Stock Notification items
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return LowStockNotificationInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array;

    /**
     * Save Low Stock Notification
     *
     * @param LowStockNotificationInterface $notification
     * @return int
     */
    public function save(LowStockNotificationInterface $notification): int;

    /**
     * Delete Low Stock Notification
     *
     * @param LowStockNotificationInterface $notification
     * @return void
     */
    public function delete(LowStockNotificationInterface $notification): void;
}
