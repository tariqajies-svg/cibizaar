<?php
/**
 * This file is part of the Magebit_Analytics package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Dunkin
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Analytics\Block;

use Magebit\Analytics\Model\Config\GtagConfig as GtagConfiguration;
use Magento\Cookie\Helper\Cookie;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * GoogleAnalytics Page Block
 *
 * @api
 */
class Ga extends Template
{
    private const DEFAULT_CURRENCY = 'USD';

    /**
     * @var GtagConfiguration
     */
    private $googleGtagConfig;

    /**
     * @var Cookie
     */
    private $cookieHelper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param GtagConfiguration $googleGtagConfig
     * @param Cookie $cookieHelper
     * @param SerializerInterface $serializer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        GtagConfiguration $googleGtagConfig,
        Cookie $cookieHelper,
        SerializerInterface $serializer,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->googleGtagConfig = $googleGtagConfig;
        $this->cookieHelper = $cookieHelper;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName(): ?string
    {
        return $this->_getData('page_name');
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     */
    public function isCookieRestrictionModeEnabled(): bool
    {
        return (bool) $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Return current website id.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCurrentWebsiteId(): int
    {
        return (int) $this->_storeManager->getWebsite()->getId();
    }

    /**
     * Return information about page for GA tracking
     *
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs
     * @link https://developers.google.com/analytics/devguides/collection/ga4
     *
     * @param string $measurementId
     * @return array
     */
    public function getPageTrackingData($measurementId): array
    {
        return [
            'optPageUrl' => $this->getOptPageUrl(),
            'measurementId' => $this->escapeHtmlAttr($measurementId, false)
        ];
    }

    /**
     * Return information about order and items for GA tracking.
     *
     * @link https://developers.google.com/analytics/devguides/collection/ga4/ecommerce#purchase
     * @link https://developers.google.com/gtagjs/reference/ga4-events#purchase
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs/enhanced-ecommerce#product-data
     * @link https://developers.google.com/gtagjs/reference/event#purchase
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrdersTrackingData(): array
    {
        $result = [];
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }
        $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            $orderIds,
            'in'
        );
        $collection = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        foreach ($collection->getItems() as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $result['products'][] = [
                    'item_id' => $this->escapeJsQuote($item->getSku()),
                    'item_name' => $this->escapeJsQuote($item->getName()),
                    'price' => number_format((float) $item->getPrice(), 2),
                    'quantity' => (int) $item->getQtyOrdered(),
                ];
            }
            $result['orders'][] = [
                'transaction_id' => $order->getIncrementId(),
                'affiliation' => $this->escapeJsQuote($this->_storeManager->getStore()->getFrontendName()),
                'value' => number_format((float) $order->getGrandTotal(), 2),
                'tax' => number_format((float) $order->getTaxAmount(), 2),
                'shipping' => number_format((float) $order->getShippingAmount(), 2),
            ];
            $result['currency'] = $order->getOrderCurrencyCode();
        }
        return $result;
    }

    /**
     * Return page url for tracking.
     *
     * @return string
     */
    private function getOptPageUrl(): string
    {
        $optPageURL = '';
        $pageName = $this->getPageName() !== null ? trim($this->getPageName()) : '';
        if ($pageName && substr($pageName, 0, 1) === '/' && strlen($pageName) > 1) {
            $optPageURL = ", '" . $this->escapeHtmlAttr($pageName, false) . "'";
        }
        return $optPageURL;
    }

    /**
     * @return string
     */
    private function getCurrentCurrency(): string
    {
        try {
            return (string) $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (LocalizedException $exception) {
            return self::DEFAULT_CURRENCY;
        }
    }

    /**
     * Provide analytics events data
     *
     * @return bool|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAnalyticsData()
    {
        $analyticData = [
            'isCookieRestrictionModeEnabled' => $this->isCookieRestrictionModeEnabled(),
            'currentWebsite' => $this->getCurrentWebsiteId(),
            'cookieName' => Cookie::IS_USER_ALLOWED_SAVE_COOKIE,
            'pageTrackingData' => $this->getPageTrackingData($this->googleGtagConfig->getMeasurementId()),
            'ordersTrackingData' => $this->getOrdersTrackingData(),
            'googleAnalyticsAvailable' => $this->googleGtagConfig->isGoogleAnalyticsAvailable(),
            'current_currency' => $this->getCurrentCurrency(),
            'is_luma'=> $this->isLuma()
        ];

        return $this->serializer->serialize($analyticData);
    }

    /**
     * @return bool
     */
    public function isLuma(): bool
    {
        return (bool) $this->googleGtagConfig->isLuma();
    }
}
