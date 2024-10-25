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

namespace Magebit\Sales\Service;

use DateInterval;
use DateTime;
use Magebit\Sales\Api\Data\ReservedOrderStatusInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventorySales\Model\GetItemsToCancelFromOrderItem;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Aheadworks\Ca\Model\Source\Role\OrderApproval\OrderStatus;

class ReservedOrderManagement
{
    private const LIMIT_PATH = 'stock/reservation/limit';
    private const TIME_LIMIT_PATH = 'stock/reservation/time';
    private const PAYMENT_METHOD_PATH = 'stock/reservation/methods';

    /**
     * ReservedOrderManagement constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem
     * @param SalesChannelInterfaceFactory $salesChannelInterfaceFactory
     * @param SalesEventInterfaceFactory $salesEventInterfaceFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent
     * @param ManagerInterface $eventManager
     * @param CacheContext $cacheContext
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly GetItemsToCancelFromOrderItem $getItemsToCancelFromOrderItem,
        private readonly SalesChannelInterfaceFactory $salesChannelInterfaceFactory,
        private readonly SalesEventInterfaceFactory $salesEventInterfaceFactory,
        private readonly WebsiteRepositoryInterface $websiteRepository,
        private readonly PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        private readonly ManagerInterface $eventManager,
        private readonly CacheContext $cacheContext
    ) {
    }

    /**
     * Process order and put it on correct status and reserve items
     *
     * @param OrderInterface $order
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processOrder(OrderInterface $order): void
    {
        if ($order->getState() !== Order::STATE_NEW ||
            $order->getOrigData('status') === ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS ||
            $order->getStatus() === ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS
        ) {
            if ($order->getOrigData('status') === ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS &&
                $order->getStatus() !== ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS
            ) {
                $this->reserveItems($order, true);
            }

            return;
        }

        $oldOrders = $this->getReservedOrders($order);
        $totalLimit = (float) $this->scopeConfig->getValue(self::LIMIT_PATH);

        $reservedOrderIds = [];
        $total = $order->getGrandTotal();
        foreach ($oldOrders as $item) {
            $reservedOrderIds[] = $item->getIncrementId();
            $total += $item->getGrandTotal();
        }
        if ($total >= $totalLimit) {
            $order->setStatus(ReservedOrderStatusInterface::PENDING_RESERVATION_STATUS);
            $order->addStatusToHistory(
                $order->getStatus(),
                sprintf('Placed order in pending reservation due to exceeding limit of %s. Total: %s, Reserved Orders - %s', $totalLimit, $total, implode(',', $reservedOrderIds))
            );
            $this->orderRepository->save($order);
            $this->reserveItems($order);
        } elseif ($order->getStatus() !== ReservedOrderStatusInterface::RESERVED_STATUS
            && $order->getStatus() !== OrderStatus::PENDING_APPROVAL
            && $order->getStatus() !== OrderStatus::REJECTED
        ) {
            $order->setStatus(ReservedOrderStatusInterface::RESERVED_STATUS);
            $this->orderRepository->save($order);

            $productIds = [];
            foreach ($order->getItems() as $item) {
                $productIds[] = $item->getProductId();

            }
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }

    /**
     * Cancel orders that are past time limit
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelOrders(): void
    {
        $dayLimit = $this->scopeConfig->getValue(self::TIME_LIMIT_PATH);
        $paymentMethods = explode(',', $this->scopeConfig->getValue(self::PAYMENT_METHOD_PATH));
        $date = new DateTime();
        $date->sub(new DateInterval(sprintf('P%sD', $dayLimit ?? 5)));

        $orders = $this->getReservedOrders(before: $date);

        foreach ($orders as $order) {
            if (in_array($order->getPayment()->getMethodInstance()->getCode(), $paymentMethods)) {
                $order->cancel();
                $order->addStatusToHistory(
                    $order->getStatus(),
                    sprintf('Canceled order due to exceeding time limit (%s day/s)', $dayLimit ?? 5)
                );
                $this->orderRepository->save($order);
            }
        }
    }

    /**
     * Get reserved qty of product
     *
     * @param ProductInterface $product
     * @return float
     */
    public function getProductCount(ProductInterface $product): float
    {
        $count = 0;
        $items = $this->getOrderItemsbyProduct($product);
        foreach ($this->getReservedOrders(orderItems: $items) as $order) {
            foreach ($order->getItems() as $item) {
                if ($item->getSku() === $product->getSku() && !$item->getParentItem()) {
                    $count += $item->getQtyOrdered();
                }
            }
        }

        return $count;
    }

    /**
     * Reserve or unreserve items
     *
     * @param OrderInterface $order
     * @param bool $reverse
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function reserveItems(OrderInterface $order, bool $reverse = false): void
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

        $salesChannel = $this->salesChannelInterfaceFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $websiteCode
            ]
        ]);

        $salesEvent = $this->salesEventInterfaceFactory->create([
            'type' => $reverse ? 'pending_unreserved' : 'pending_reserved',
            'objectType' => SalesEventInterface::OBJECT_TYPE_ORDER,
            'objectId' => (string) $order->getEntityId()
        ]);

        /** @var OrderItem $item */
        foreach ($order->getItems() as $item) {
            $itemsToCancel = $this->getItemsToCancelFromOrderItem->execute($item);
            if ($reverse) {
                foreach ($itemsToCancel as $cancelItem) {
                    $cancelItem->setQuantity(-$cancelItem->getQuantity());
                }
            }
            $this->placeReservationsForSalesEvent->execute($itemsToCancel, $salesChannel, $salesEvent);
        }
    }

    /**
     * Get Reserved items
     *
     * @param OrderInterface|null $currentOrder
     * @param DateTime|null $before
     * @return OrderInterface[]
     * @param array|null $orderItems
     * @return OrderInterface[]
     */
    private function getReservedOrders(?OrderInterface $currentOrder = null, ?DateTime $before = null, ?array $orderItems = null): array
    {
        $criteria =  $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::STATUS, ReservedOrderStatusInterface::RESERVED_STATUS);

        if ($currentOrder) {
            $criteria->addFilter(OrderInterface::CUSTOMER_ID, $currentOrder->getCustomerId())
                ->addFilter(OrderInterface::ENTITY_ID, $currentOrder->getEntityId(), 'neq')
                ->addFilter(OrderInterface::CREATED_AT, $currentOrder->getCreatedAt(), 'lt');
        }

        if ($before) {
            $criteria->addFilter(OrderInterface::CREATED_AT, $before, 'lt');
        }

        if ($orderItems) {
            $orderIds = [];
            foreach ($orderItems as $orderItem) {
                $orderIds[] = $orderItem->getOrderId();
            }
            $criteria->addFilter(OrderInterface::ENTITY_ID, $orderIds, 'in');
        }

        return $this->orderRepository->getList($criteria->create())->getItems();
    }

    /**
     * Get order items by product
     *
     * @param ProductInterface $product
     * @return array
     */
    public function getOrderItemsbyProduct(ProductInterface $product): array
    {
        $criteria =  $this->searchCriteriaBuilder
            ->addFilter(OrderItemInterface::PRODUCT_ID, $product->getId());

        return $this->orderItemRepository->getList($criteria->create())->getItems();
    }
}
