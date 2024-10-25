<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\ViewModel;

use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class TierPrices implements ArgumentInterface
{
    private const XML_PATH = 'catalog/frontend/bulk_price_text';
    private ?bool $isTierPriceAllowed = null;

    /**
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected readonly Session $customerSession,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isTierPriceAllowed(): bool
    {
        if ($this->isTierPriceAllowed === null) {
            $this->isTierPriceAllowed = $this->customerSession->getCustomerGroupId() !== GroupManagement::NOT_LOGGED_IN_ID;
        }

        return $this->isTierPriceAllowed;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBulkPriceMessage(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        ) ?? '';
    }
}
