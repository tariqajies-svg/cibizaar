<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_CONFIG_PAYMENT__NGENIUSONLINE__BASE_CURRENCY = 'payment/ngeniusonline/base_currency';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param int|null $store
     * @return bool
     */
    public function isPaymentNgeniusOnlineBaseCurrency(int $store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_CONFIG_PAYMENT__NGENIUSONLINE__BASE_CURRENCY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
