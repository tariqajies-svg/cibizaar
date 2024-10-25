<?php
/**
 * This file is part of the Magebit_GeoIPCurrency package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\GeoIPCurrency\Model;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;

class GeoIPCurrencyHttp
{
    public const COOKIE_GEOIP_CURRENCY_STRING = 'X-Magento-GeoIP-Currency';
    public const HEADER_GEOIP_CURRENCY_STRING = 'X-Magento-GeoIP-Currency';
    public const HEADER_GEOIP_CURRENCY_UPDATE_STRING = 'X-Magento-GeoIP-Currency-Update';

    /**
     * @param Context $context
     * @param ConfigInterface $sessionConfig
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param HttpRequest $request
     */
    public function __construct(
        protected readonly Context $context,
        protected readonly ConfigInterface $sessionConfig,
        protected readonly CookieMetadataFactory $cookieMetadataFactory,
        protected readonly CookieManagerInterface $cookieManager,
        protected readonly HttpRequest $request
    ) {
    }

    /**
     * @return string|null
     */
    protected function getHashedCurrencyCode(): ?string
    {
        $currencyCode = $this->context->getValue(Context::CONTEXT_CURRENCY);

        // Technically, currency code always exists, but to avoid any errors, we are adding this check.
        if (!$currencyCode) {
            return null;
        }

        return $currencyCode;
    }

    /**
     * Send GeoIP Currency Cookie
     *
     * This method was written same way as from Magento core: \Magento\Framework\App\Response\Http::sendVary
     *
     * @throws FailureToSendException
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     */
    public function sendGeoIPCurrencyCookie(): void
    {
        $geoIpString = $this->getHashedCurrencyCode();
        if ($geoIpString) {
            $cookieLifeTime = $this->sessionConfig->getCookieLifetime();
            $sensitiveCookMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata(
                [
                    CookieMetadata::KEY_DURATION => $cookieLifeTime,
                    CookieMetadata::KEY_SAME_SITE => 'Lax'
                ]
            )->setPath('/');
            $this->cookieManager->setSensitiveCookie(
                self::COOKIE_GEOIP_CURRENCY_STRING,
                $geoIpString,
                $sensitiveCookMetadata
            );
        } elseif ($this->request->get(self::COOKIE_GEOIP_CURRENCY_STRING)) {
            $cookieMetadata = $this->cookieMetadataFactory->createSensitiveCookieMetadata()->setPath('/');
            $this->cookieManager->deleteCookie(self::COOKIE_GEOIP_CURRENCY_STRING, $cookieMetadata);
        }
    }
}
