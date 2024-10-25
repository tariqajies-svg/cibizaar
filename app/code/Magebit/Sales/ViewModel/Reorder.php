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

namespace Magebit\Sales\ViewModel;

use Aheadworks\Ca\Model\Role\OrderApproval\IsActiveChecker;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * Sales module base helper
 */
class Reorder implements ArgumentInterface
{
    private const XML_PATH_SALES_REORDER_ALLOW = 'sales/reorder/allow';

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var UrlHelper
     */
    private UrlHelper $urlHelper;

    /**
     * @var IsActiveChecker
     */
    private IsActiveChecker $isActiveChecker;

    /**
     * @param Session $customerSession
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlHelper $urlHelper
     * @param IsActiveChecker $isActiveChecker
     */
    public function __construct(
        Session $customerSession,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $scopeConfig,
        UrlHelper $urlHelper,
        IsActiveChecker $isActiveChecker
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->urlHelper = $urlHelper;
        $this->isActiveChecker = $isActiveChecker;
    }

    /**
     * @return bool
     */
    public function isAllow(): bool
    {
        return $this->isAllowed();
    }

    /**
     * Check if reorder is allowed for given store
     *
     * @param int|Store|null $store
     * @return bool
     */
    public function isAllowed(Store|int|null $store = null): bool
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_SALES_REORDER_ALLOW,
            ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }

        return false;
    }

    /**
     * Check is it possible to reorder
     *
     * @param int $orderId
     * @return bool
     */
    public function canReorder(int $orderId): bool
    {
        $order = $this->orderRepository->get($orderId);
        if (!$this->isAllowed($order->getStore()) || $this->isActiveChecker->isOrderRejected($order)) {
            return false;
        }
        if ($this->customerSession->isLoggedIn()) {
            return $order->canReorder();
        } else {
            return true;
        }
    }

    /**
     * Get data for post by javascript in format acceptable to $.mage.dataPost widget
     *
     * @param string $url
     * @param array $data
     * @return string
     */
    public function getPostData(string $url, array $data = []): string
    {
        if (!isset($data[ActionInterface::PARAM_NAME_URL_ENCODED])) {
            $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl();
        }

        return json_encode(['action' => $url, 'data' => $data]);
    }
}
