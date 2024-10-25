<?php
/**
 * This file is part of the Magebit_GeoIPCurrency package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\GeoIPCurrency\Service;

use Exception;
use Magebit\GeoIPCurrency\Model\GeoIPCurrencyHttp;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SetCurrencyBasedOnGeoIPCountry
{
    /**
     * @param Request $request
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly Request $request,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly CookieManagerInterface $cookieManager,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        // Get currency which was set by Varnish
        $geoIPCurrencyUpdate = $this->request->getHeader(GeoIPCurrencyHttp::HEADER_GEOIP_CURRENCY_UPDATE_STRING);
        if (!$geoIPCurrencyUpdate) {
            return;
        }

        // Double check if header with GeoIP currency exists.
        // This header populates from Varnish config.
        $geoIPCurrency = $this->request->getHeader(GeoIPCurrencyHttp::HEADER_GEOIP_CURRENCY_STRING);
        if (!$geoIPCurrency) {
            return;
        }

        try {
            $this->storeManager->getStore()->setCurrentCurrencyCode($geoIPCurrency);
        } catch (Exception $e) {
            $this->logger->error('Cannot set GeoIP currency due to error:' . $e->getMessage());
        }
    }
}
