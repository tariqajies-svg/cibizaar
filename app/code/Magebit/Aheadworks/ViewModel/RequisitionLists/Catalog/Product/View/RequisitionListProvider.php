<?php
/**
 * This file is part of the Magebit_Aheadworks package.
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

namespace Magebit\Aheadworks\ViewModel\RequisitionLists\Catalog\Product\View;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\RequisitionLists\Model\Product\Checker\ProhibitedTypeChecker;
use Aheadworks\RequisitionLists\Model\Service\CustomerService;
use Aheadworks\RequisitionLists\Model\Url;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\RequisitionLists\ViewModel\Catalog\Product\View\RequisitionListProvider
    as RequisitionListProviderViewModel;

class RequisitionListProvider extends RequisitionListProviderViewModel
{
    /** @var CustomerService  */
    private CustomerService $customerService;
    /** @var StoreManagerInterface  */
    private StoreManagerInterface $storeManager;
    /** @var CompanyUserManagementInterface  */
    private CompanyUserManagementInterface $companyUserManagement;
    /** @var ScopeConfigInterface  */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param Url $urlBuilder
     * @param CustomerService $customerService
     * @param StoreManagerInterface $storeManager
     * @param SessionFactory $sessionFactory
     * @param ProhibitedTypeChecker $prohibitedTypeChecker
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Url $urlBuilder,
        CustomerService $customerService,
        StoreManagerInterface $storeManager,
        SessionFactory $sessionFactory,
        ProhibitedTypeChecker $prohibitedTypeChecker,
        CompanyUserManagementInterface $companyUserManagement,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($urlBuilder, $customerService, $storeManager, $sessionFactory, $prohibitedTypeChecker);
        $this->customerService = $customerService;
        $this->storeManager = $storeManager;
        $this->companyUserManagement = $companyUserManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function getIsEnabled(): bool
    {
        return $this->companyUserManagement->getCurrentUser() && $this->customerService->isActiveForCurrentWebsite(
            $this->storeManager->getWebsite()->getId()
        );
    }
}
