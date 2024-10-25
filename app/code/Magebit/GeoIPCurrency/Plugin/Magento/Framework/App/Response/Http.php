<?php
/**
 * This file is part of the Magebit_GeoIPCurrency package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\GeoIPCurrency\Plugin\Magento\Framework\App\Response;

use Magebit\GeoIPCurrency\Model\GeoIPCurrencyHttp;
use Magento\Framework\App\PageCache\NotCacheableInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

class Http
{
    /**
     * @param GeoIPCurrencyHttp $geoIPCurrencyHttp
     */
    public function __construct(
        protected readonly GeoIPCurrencyHttp $geoIPCurrencyHttp
    ) {
    }

    /**
     * Set proper value of X-Magento-GeoIP-Currency cookie.
     *
     * @param HttpResponse $subject
     * @return void
     *
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function beforeSendResponse(HttpResponse $subject): void
    {
        if ($subject instanceof NotCacheableInterface || $subject->headersSent()) {
            return;
        }

        $this->geoIPCurrencyHttp->sendGeoIPCurrencyCookie();
    }
}
