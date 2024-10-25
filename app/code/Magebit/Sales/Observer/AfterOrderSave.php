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

namespace Magebit\Sales\Observer;

use Magebit\Sales\Api\Data\ReservedOrderStatusInterface;
use Magebit\Sales\Service\ReservedOrderManagement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class AfterOrderSave implements ObserverInterface
{
    /**
     * AfterOrderSave constructor
     *
     * @param ReservedOrderManagement $reservedOrderManagement
     */
    public function __construct(
        private readonly ReservedOrderManagement $reservedOrderManagement
    ) {
    }

    /**
     * Process orders after save
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');

        $this->reservedOrderManagement->processOrder($order);
    }
}
