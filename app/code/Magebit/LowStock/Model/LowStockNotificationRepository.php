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

namespace Magebit\LowStock\Model;

use Exception;
use Magebit\LowStock\Api\Data\LowStockNotificationInterface;
use Magebit\LowStock\Api\LowStockNotificationRepositoryInterface;
use Magebit\LowStock\Model\ResourceModel\LowStockNotificationModel\LowStockNotificationCollectionFactory;
use Magebit\LowStock\Model\ResourceModel\LowStockNotificationResource as LowStockNotificationResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class LowStockNotificationRepository implements LowStockNotificationRepositoryInterface
{
    /**
     * @var CollectionProcessor
     */
    private CollectionProcessor $collectionProcessor;
    /**
     * @var LowStockNotificationResource
     */
    private LowStockNotificationResource $lowStockResource;
    /**
     * @var LowStockNotificationCollectionFactory
     */
    private LowStockNotificationCollectionFactory $collectionFactory;

    /**
     * LowStockNotificationRepository constructor
     *
     * @param CollectionProcessor $collectionProcessor
     * @param LowStockNotificationResource $lowStockResource
     * @param LowStockNotificationCollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionProcessor $collectionProcessor,
        LowStockNotificationResource $lowStockResource,
        LowStockNotificationCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->lowStockResource = $lowStockResource;
    }

    /**
     * Get Low Stock Notification items
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array|LowStockNotificationInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria): array
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getItems();
    }

    /**
     * Save Low Stock Notification
     *
     * @param LowStockNotificationInterface $notification
     * @return int
     * @throws AlreadyExistsException
     */
    public function save(LowStockNotificationInterface $notification): int
    {
        $this->lowStockResource->save($notification);

        return (int) $notification->getId();
    }

    /**
     * Delete Low Stock Notification
     *
     * @param LowStockNotificationInterface $notification
     * @return void
     * @throws Exception
     */
    public function delete(LowStockNotificationInterface $notification): void
    {
        $this->lowStockResource->delete($notification);
    }
}
