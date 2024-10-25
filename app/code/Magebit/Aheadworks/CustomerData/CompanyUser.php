<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Aheadworks
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Aheadworks\CustomerData;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Aheadworks\Ctq\ViewModel\Checkout\RequestQuoteLink;
use Magebit\HyvaAheadworksCtq\ViewModel\PermissionChecker;

class CompanyUser implements SectionSourceInterface
{
    private const COMPANY_USER_KEY = 'isCompanyUser';
    private const QUOTE_AVAILABLE_KEY = 'isQuoteAvailable';

    /**
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param RequestQuoteLink $requestQuoteLink
     * @param PermissionChecker $permissionChecker
     */
    public function __construct(
        private readonly CompanyUserManagementInterface $companyUserManagement,
        private readonly RequestQuoteLink $requestQuoteLink,
        private readonly PermissionChecker $permissionChecker
    ) {
    }

    /**
     * @return bool[]
     */
    public function getSectionData(): array
    {
        return [
            self::COMPANY_USER_KEY => !!$this->companyUserManagement->getCurrentUser(),
            self::QUOTE_AVAILABLE_KEY => $this->isQuoteAvailable()
        ];
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isQuoteAvailable(): bool
    {
        return $this->requestQuoteLink->isRequestQuoteAvailable() &&
            $this->permissionChecker->isQuoteAllowed() &&
            $this->permissionChecker->isCurrentUserAllowedQuote() &&
            $this->requestQuoteLink->isRequestQuoteAvailable();
    }
}
