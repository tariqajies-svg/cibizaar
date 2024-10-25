<?php
/**
 * This file is part of the Magebit_Customer package.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\Customer\Service;

use DateTime;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Helper\Reorder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory as PaymentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as OrderStatusCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrdersFiltersService
{
    private const FILTER_TYPE_LIKE = 'like';
    private const FILTER_TYPE_LTEQ = 'lteq';
    private const FILTER_TYPE_GTEQ = 'gteq';
    private const FILTER_TYPE_INVOICE = 'fromInvoiceFactory';
    private const FILTER_TYPE_ITEM = 'fromItemFactory';
    private const FILTER_TYPE_PAYMENT = 'fromPaymentFactory';
    private const FILTER_TYPE_NIN = 'nin';
    private const FILTER_TYPE_EQ = 'eq';
    private const FILTER_TYPE_LT = 'lt';
    private const FILTER_TYPE_GT = 'gt';
    private const FILTER_TYPE_IN = 'in';

    private const ORDER_ID_KEY = 'order_id';
    private const PARENT_ID_KEY = 'parent_id';

    private const FILTER_PARAM_PAGE = 'page';
    private const FILTER_PARAM_INVOICE = 'invoice_id';
    private const FILTER_PARAM_ITEM_NAME = 'item_name';
    private const FILTER_PARAM_DATE_FROM = 'date_from';
    private const FILTER_PARAM_DATE_TO = 'date_to';
    private const FILTER_PARAM_TOTAL_FROM = 'total_from';
    private const FILTER_PARAM_TOTAL_TO = 'total_to';
    private const FILTER_PARAM_ORDER_STATUS = 'order_status';
    private const FILTER_PARAM_ORDER_ID = 'order_id';

    private const SELECT_FIELDS_FROM_ORDERS = [
        'entity_id',
        'created_at',
        'order_currency_code',
        'grand_total',
        'total_due',
        'customer_firstname',
        'customer_lastname',
        'increment_id',
        'status',
        'state'
    ];

    private const REORDER_URL_PATH = 'sales/order/reorder';

    private const PROPERTY_PO_NUMBER = 'po_number';
    private const PROPERTY_NAME = 'name';
    private const PROPERTY_INCREMENT_ID = 'increment_id';
    private const PROPERTY_CREATED_AT = 'main_table.created_at';
    private const PROPERTY_GRAND_TOTAL = 'grand_total';
    private const PROPERTY_STATUS = 'status';

    /**
     * @var CollectionFactoryInterface
     */
    private CollectionFactoryInterface $collection;

    /**
     * @var InvoiceCollectionFactory
     */
    private InvoiceCollectionFactory $invoiceCollectionFactory;

    /**
     * @var ItemCollectionFactory
     */
    private ItemCollectionFactory $itemCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var Reorder
     */
    private Reorder $helperReorder;

    /**
     * @var PostHelper
     */
    private PostHelper $postHelper;

    /**
     * @var PaymentCollectionFactory
     */
    private PaymentCollectionFactory $paymentCollectionFactory;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * @var OrderStatusCollection
     */
    private OrderStatusCollection $orderStatusCollection;

    /**
     * @var OrderStatusCollectionFactory
     */
    private OrderStatusCollectionFactory $orderStatusCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param CollectionFactoryInterface $collection
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param UrlInterface $urlBuilder
     * @param Reorder $helperReorder
     * @param PostHelper $postHelper
     * @param PaymentCollectionFactory $paymentCollectionFactory
     * @param PriceHelper $priceHelper
     * @param OrderStatusCollection $orderStatusCollection
     */
    public function __construct(
        CollectionFactoryInterface $collection,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        UrlInterface $urlBuilder,
        Reorder $helperReorder,
        PostHelper $postHelper,
        PaymentCollectionFactory $paymentCollectionFactory,
        PriceHelper $priceHelper,
        OrderStatusCollection $orderStatusCollection,
        OrderStatusCollectionFactory $orderStatusCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        $this->collection = $collection;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->urlBuilder = $urlBuilder;
        $this->helperReorder = $helperReorder;
        $this->postHelper = $postHelper;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * Add type and search property to params
     *
     * @param object $params
     * @return object
     */
    public function addTypeAndSearchPropertyToParams(object $params): object
    {
        foreach ($params as $paramKey => $param) {
            switch ($paramKey) {
                case self::FILTER_PARAM_ORDER_ID:
                    $params->$paramKey->type = self::FILTER_TYPE_LIKE;
                    $params->$paramKey->searchProperty = self::PROPERTY_INCREMENT_ID;
                    break;
                case self::FILTER_PARAM_INVOICE:
                    $params->$paramKey->type = self::FILTER_TYPE_INVOICE;
                    $params->$paramKey->searchProperty = self::PROPERTY_INCREMENT_ID;
                    $params->$paramKey->orderProperty = self::ORDER_ID_KEY;
                    break;
                case self::FILTER_PARAM_ITEM_NAME:
                    $params->$paramKey->type = self::FILTER_TYPE_ITEM;
                    $params->$paramKey->searchProperty = self::PROPERTY_NAME;
                    $params->$paramKey->orderProperty = self::ORDER_ID_KEY;
                    break;
                case self::PROPERTY_PO_NUMBER:
                    $params->$paramKey->type = self::FILTER_TYPE_PAYMENT;
                    $params->$paramKey->searchProperty = self::PROPERTY_PO_NUMBER;
                    $params->$paramKey->orderProperty = self::PARENT_ID_KEY;
                    break;
                case self::FILTER_PARAM_DATE_FROM:
                    $params->$paramKey->type = self::FILTER_TYPE_GTEQ;
                    $params->$paramKey->searchProperty = self::PROPERTY_CREATED_AT;
                    break;
                case self::FILTER_PARAM_DATE_TO:
                    $params->$paramKey->type = self::FILTER_TYPE_LTEQ;
                    $params->$paramKey->searchProperty = self::PROPERTY_CREATED_AT;
                    break;
                case self::FILTER_PARAM_TOTAL_FROM:
                    $params->$paramKey->type = self::FILTER_TYPE_GTEQ;
                    $params->$paramKey->searchProperty = self::PROPERTY_GRAND_TOTAL;
                    break;
                case self::FILTER_PARAM_TOTAL_TO:
                    $params->$paramKey->type = self::FILTER_TYPE_LTEQ;
                    $params->$paramKey->searchProperty = self::PROPERTY_GRAND_TOTAL;
                    break;
                case self::FILTER_PARAM_ORDER_STATUS:
                    $params->$paramKey->type = self::FILTER_TYPE_EQ;
                    $params->$paramKey->searchProperty = self::PROPERTY_STATUS;
                    if ($param->value) {
                        $params->$paramKey->label = $this->getOrderStatusFELabel($param->value) ?? $this->orderStatusCollection->toOptionHash()[$param->value];
                    }
                    break;
                case self::FILTER_PARAM_PAGE:
                    $params->$paramKey->type = self::FILTER_PARAM_PAGE;
                    if ($param->value === null) {
                        $params->$paramKey->value = 1;
                    }
                    break;
            }
        }

        return $params;
    }

    /**
     * Create params object fo FE side
     *
     * We need these options on the FE side because we need to show applied filters
     *
     * @param object $params
     * @return object
     */
    public function paramsForFE(object $params): object
    {
        foreach ($params as $paramKey => $param) {
            if (isset($param->type)) {
                unset($params->$paramKey->type);
            }

            if (isset($param->searchProperty)) {
                unset($params->$paramKey->searchProperty);
            }
        }

        return $params;
    }

    /**
     * Create search params from search query
     *
     * @param string $searchQuery
     * @return array
     */
    public function createSearchParams(string $searchQuery): array
    {
        $searchWords = explode(',', $searchQuery);
        foreach ($searchWords as $key => $searchWord) {
            $searchWords[$key] = ['word' => trim($searchWord), 'state' => 'unused'];
        }

        return $searchWords;
    }

    /**
     * Get collection after filters
     *
     * This method accepts a set of search options and a user ID.
     * It then examines each parameter and adds the filter that is assigned to that parameter.
     * The method returns a collection of elements with all filters applied
     *
     * @param object $params
     * @param int $customerId
     * @return Collection|null
     */
    public function getFilteredCollection(object $params, int $customerId): ?Collection
    {
        $collection = $this->collection->create($customerId)
            ->addFieldToSelect(self::SELECT_FIELDS_FROM_ORDERS)
            ->setOrder(self::PROPERTY_CREATED_AT, 'desc');
        $allOrdersIds = $collection->getAllIds();

        // Filter out invoice payment orders
        $collection->getSelect()->join(['order_item' => 'sales_order_item'], 'main_table.entity_id = order_item.order_id');
        $collection->addFieldToFilter('order_item.sku', ['neq' => 'orders']);
        $collection->getSelect()->group('main_table.entity_id');

        foreach ($params as $param) {
            if ($param->value === null || $param->value === false || $param->value === '') {
                continue;
            }
            if ($collection && isset($param->type)) {
                switch ($param->type) {
                    case self::FILTER_TYPE_LIKE:
                        $collection->addAttributeToFilter($param->searchProperty, [self::FILTER_TYPE_LIKE => '%' . $param->value . '%']);
                        break;
                    case self::FILTER_TYPE_NIN:
                        $collection->addAttributeToFilter($param->searchProperty, [self::FILTER_TYPE_NIN => $param->filterArr]);
                        break;
                    case self::FILTER_TYPE_EQ:
                        $collection->addAttributeToFilter($param->searchProperty, [self::FILTER_TYPE_EQ => $param->value]);
                        break;
                    case self::FILTER_TYPE_GTEQ:
                        $collection->addAttributeToFilter($param->searchProperty, [[self::FILTER_TYPE_GT => $param->value], [self::FILTER_TYPE_EQ => $param->value]]);
                        break;
                    case self::FILTER_TYPE_LTEQ:
                        $collection->addAttributeToFilter($param->searchProperty, [[self::FILTER_TYPE_LT => $param->value], [self::FILTER_TYPE_EQ => $param->value]]);
                        break;
                    case self::FILTER_TYPE_ITEM:
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $param->value . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->itemCollectionFactory->create(),
                            $param->orderProperty
                        );
                        break;
                    case self::FILTER_TYPE_INVOICE:
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $param->value . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->invoiceCollectionFactory->create(),
                            $param->orderProperty
                        );
                        break;
                    case self::FILTER_TYPE_PAYMENT:
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $param->value . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->paymentCollectionFactory->create(),
                            $param->orderProperty
                        );
                        break;
                    case self::FILTER_PARAM_PAGE:
                        $collection->setPage($param->value, $param->pageSize);
                        $params->page->countPages = $collection->getLastPageNumber();
                        break;
                }
            }
        }

        $params->page->totalRecords = $collection->getSize();

        return $collection;
    }

    /**
     * Compare params with keywords
     *
     * This method accepts a set of search parameters, search words, and a user ID.
     * Next, it iterates through all the words and creates a collection, checking for a match for a given word.
     * If the number of elements in the collection is greater than zero, then this word is filled in the set of parameters in the property for which a match was found.
     * If there are no matches, then the unused mark remains on the given word and the entire search result returns an empty set of orders.
     * If matches are found for all words, then a set of parameters with these words is formed.
     *
     * @param object $params
     * @param array $words
     * @param int $customerId
     * @return object
     */
    public function compareParamWithWords(object $params, array $words, int $customerId): object
    {
        $paramsClone = clone $params;
        foreach ($paramsClone as $key => $param) {
            if ($key !== self::FILTER_PARAM_PAGE) {
                $paramsClone->$key->value = null;
            }
        }

        foreach ($words as $keyWord => $word) {
            foreach ($paramsClone as $key => $param) {
                if (!isset($param->type)) {
                    continue;
                }
                switch ($param->type) {
                    case self::FILTER_TYPE_LIKE:
                        $collection = $this->collection->create($customerId)->addAttributeToSelect(self::ORDER_ID_KEY);
                        $collection->addAttributeToFilter($param->searchProperty, [self::FILTER_TYPE_LIKE => '%' . $word['word'] . '%']);

                        if ($collection->getSize() > 0 && $param->value === null) {
                            $params->$key->value = $word['word'];
                            $words[$keyWord]['state'] = 'used';
                            continue 3;
                        }

                        unset($collection);
                        break;
                    case self::FILTER_TYPE_ITEM:
                        $collection = $this->collection->create($customerId)->addAttributeToSelect(self::ORDER_ID_KEY);
                        $allOrdersIds = $collection->getAllIds();
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $word['word'] . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->itemCollectionFactory->create(),
                            $param->orderProperty
                        );

                        if ($collection && $collection->getSize() > 0 && $param->value === null) {
                            $params->$key->value = $word['word'];
                            $words[$keyWord]['state'] = 'used';
                            continue 3;
                        }

                        unset($collection);
                        break;
                    case self::FILTER_TYPE_INVOICE:
                        $collection = $this->collection->create($customerId)->addAttributeToSelect(self::ORDER_ID_KEY);
                        $allOrdersIds = $collection->getAllIds();
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $word['word'] . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->invoiceCollectionFactory->create(),
                            $param->orderProperty
                        );

                        if ($collection && $collection->getSize() > 0 && $param->value === null) {
                            $params->$key->value = $word['word'];
                            $words[$keyWord]['state'] = 'used';
                            continue 3;
                        }

                        unset($collection);
                        break;
                    case self::FILTER_TYPE_PAYMENT:
                        $collection = $this->collection->create($customerId)->addAttributeToSelect(self::ORDER_ID_KEY);
                        $allOrdersIds = $collection->getAllIds();
                        $collection = $this->getCollectionFromFactory(
                            $collection,
                            [
                                $param->searchProperty => [self::FILTER_TYPE_LIKE => '%' . $word['word'] . '%'],
                                $param->orderProperty => [self::FILTER_TYPE_IN => $allOrdersIds]
                            ],
                            [$param->orderProperty, $param->searchProperty],
                            $this->paymentCollectionFactory->create(),
                            $param->orderProperty
                        );

                        if ($collection && $collection->getSize() > 0 && $param->value === null) {
                            $params->$key->value = $word['word'];
                            $words[$keyWord]['state'] = 'used';
                            continue 3;
                        }

                        unset($collection);
                        break;
                }
            }
        }

        foreach ($words as $word) {
            if ($word['state'] === 'unused') {
                $params->nullableCollection = true;
            }
        }

        unset($collection);

        return $params;
    }

    /**
     * Get orders collection from another collection
     *
     * This method is needed to search for orders whose parameters need to be found in other tables.
     * Right now it checks tables like sales_order_payment, sales_invoice and sales_order_item
     *
     * @param Collection $collection
     * @param array $filter
     * @param array $select
     * @param InvoiceCollection|ItemCollection|PaymentCollection $collectionFactory
     * @param string $orderProperty
     * @return Collection|null
     */
    private function getCollectionFromFactory(
        Collection $collection,
        array $filter,
        array $select,
        $collectionFactory,
        string $orderProperty
    ): ?Collection {
        if (!$collectionFactory) {
            return null;
        }

        $collectionFactory->addFieldToSelect($select);

        foreach ($filter as $key => $rule) {
            $collectionFactory->addAttributeToFilter($key, $rule);
        }

        $orders = $collectionFactory->getColumnValues($orderProperty);

        if (count($orders) === 0) {
            return null;
        }

        return $collection->addAttributeToFilter('entity_id', [self::FILTER_TYPE_IN => $orders]);
    }

    /**
     * Make collection data array
     *
     * @param Collection $collection
     * @return array
     * @throws Exception
     */
    public function makeCollectionDataArray(Collection $collection): array
    {
        $result = [];

        /** @var Order $order */
        foreach ($collection as $order) {
            $displayDate = (new DateTime($order->getCreatedAt()))->format('d/m/Y');
            $invoiceCollection = $order->getInvoiceCollection();

            $order->setData('payment_method', $order->getPayment()->getMethodInstance()->getCode());
            $order->setData('display_created_at', $displayDate);
            $order->setData('invoice_number', $invoiceCollection->getItems() ? $invoiceCollection->getFirstItem()->getIncrementId() : '');
            $order->setData('status_label', $order->getStatusLabel());
            $order->setData('currency_symbol', $this->priceCurrency->getCurrencySymbol(null, $order->getOrderCurrencyCode()));
            $order->setData('grand_total', $order->formatPrice($order->getGrandTotal()));

            if ($this->helperReorder->canReorder($order->getId())) {
                $order->setData('reorder_data', json_decode($this->getPostData($order), false, 20, JSON_THROW_ON_ERROR));
            }

            $result[] = $order->getData();
        }

        return $result;
    }

    /**
     * Get Customer Id
     *
     * @param CustomerInterface $customer
     * @return int
     */
    public function getCustomerId(CustomerInterface $customer): int
    {
        $customerId = (int)$customer->getId();

        if ($customer->getCustomAttribute(self::PARENT_ID_KEY)
            && $customer->getCustomAttribute(self::PARENT_ID_KEY)->getValue()) {
            $customerId = (int)$customer->getCustomAttribute(self::PARENT_ID_KEY)->getValue();
        }

        return $customerId;
    }

    /**
     * Get post data
     *
     * @param Order $order
     * @return string
     */
    private function getPostData(Order $order): string
    {
        $reorderUrl = $this->urlBuilder->getUrl(self::REORDER_URL_PATH, [self::ORDER_ID_KEY => $order->getId()]);
        return $this->postHelper->getPostData($reorderUrl, []);
    }

    /**
     * Get Store specific status label
     *
     * @param string $statusValue
     * @return Phrase|string|null
     * @throws NoSuchEntityException
     */
    private function getOrderStatusFELabel(string $statusValue)
    {
        /** @var Order\Status $status */
        $status = $this->orderStatusCollectionFactory->create()
            ->addFilter('status', $statusValue)->getFirstItem();

        if ($status) {
            return $status->getStoreLabel($this->storeManager->getStore()) ?? $status->getLabel();
        }

        return null;
    }
}
