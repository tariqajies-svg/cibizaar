<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCtq package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCreditLimit
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\HyvaAheadworksCtq\ViewModel\Customer;

use Aheadworks\Ctq\Api\BuyerActionManagementInterface;
use Aheadworks\Ctq\Api\QuoteRepositoryInterface;
use Aheadworks\Ctq\Model\History\Notifier\RecipientResolver;
use Aheadworks\Ctq\Model\Order\DataProvider as OrderDataProvider;
use Aheadworks\Ctq\Model\Quote\Url;
use Aheadworks\Ctq\Model\Source\Quote\Status as StatusSource;
use Aheadworks\Ctq\Model\ThirdPartyModule\Aheadworks\Ca\CompanyManagement;
use Aheadworks\Ctq\ViewModel\Customer\Quote as QuoteParent;
use IntlDateFormatter;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;

class Quote extends QuoteParent
{
    // @codingStandardsIgnoreStart
    public function __construct(
        StatusSource $statusSource,
        PriceCurrencyInterface $priceCurrency,
        private readonly TimezoneInterface $localeDate,
        UrlInterface $urlBuilder,
        Url $url,
        BuyerActionManagementInterface $buyerActionManagement,
        OrderDataProvider $orderDataProvider,
        CustomerSession $customerSession,
        QuoteRepositoryInterface $quoteRepository,
        CompanyManagement $companyManagement,
        RecipientResolver $recipientResolver
    ) {
        parent::__construct(
            $statusSource,
            $priceCurrency,
            $localeDate,
            $urlBuilder,
            $url,
            $buyerActionManagement,
            $orderDataProvider,
            $customerSession,
            $quoteRepository,
            $companyManagement,
            $recipientResolver
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve formatted created at date
     *
     * @param string $createdAt
     * @param bool $addComma
     * @return string
     */
    public function getCreatedAtFormatted($createdAt, bool $addComma = true): string
    {
        return $this->localeDate->formatDateTime(
            $createdAt,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            null,
            null,
            $addComma ? 'dd/MM/yyyy, hh:mm:ssa' : 'dd/MM/yyyy hh:mm:ssa'
        );
    }

    /**
     * Retrieve formatted last updated at date
     *
     * @param string $lastUpdatedAt
     * @param bool $addComma
     * @return string
     */
    public function getLastUpdatedAtFormatted($lastUpdatedAt, bool $addComma = true): string
    {
        return $this->localeDate->formatDateTime(
            $lastUpdatedAt,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            null,
            null,
            $addComma ? 'dd/MM/yyyy, hh:mm:ssa' : 'dd/MM/yyyy hh:mm:ssa'
        );
    }

    /**
     * Retrieve formatted expired date
     *
     * @param string $expiredDate
     * @param bool $addComma
     * @return string
     */
    public function getExpiredDateFormatted($expiredDate, bool $addComma = true): string
    {
        return $this->localeDate->formatDateTime(
            $expiredDate,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            null,
            null,
            $addComma ? 'dd/MM/yy, hh:mm:ssa' : 'dd/MM/yy hh:mm:ssa'
        );
    }

    /**
     * @param string $statusCode
     * @return string
     */
    public function getStatusColor(string $statusCode): string
    {
        $colorsClasses = [
            StatusSource::PENDING_SELLER_REVIEW => 'warning',
            StatusSource::DECLINED_BY_SELLER => 'error',
            StatusSource::PENDING_BUYER_REVIEW => 'warning',
            StatusSource::DECLINED_BY_BUYER => 'error',
            StatusSource::ORDERED => 'warning',
            StatusSource::EXPIRED => 'error',
            StatusSource::ACCEPTED => 'success'
        ];

        return $colorsClasses[$statusCode];
    }
}
