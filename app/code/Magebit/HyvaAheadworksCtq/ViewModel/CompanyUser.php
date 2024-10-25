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

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCtq\ViewModel;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model to get company user information
 */
class CompanyUser extends DataObject implements ArgumentInterface
{
    /**
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param array $data
     */
    public function __construct(
        protected readonly CompanyUserManagementInterface $companyUserManagement,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCurrentCompanyUser(): ?CustomerInterface
    {
        return $this->companyUserManagement->getCurrentUser();
    }
}
