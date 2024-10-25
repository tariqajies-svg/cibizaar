<?php
/**
 * This file is part of the Magebit_OrderStatusColor package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_OrderStatusColor
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
namespace Magebit\Customer\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderStatus implements ArgumentInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Get all order statuses as key-value pair
     *
     * @return array
     */
    public function getStatusListForFilter(): array
    {
        return $this->collectionFactory->create()->getItems();
    }

    /**
     * Get current store ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }
}
