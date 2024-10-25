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

namespace Magebit\Aheadworks\Plugin\RequisitionList\Controller;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\RequisitionLists\Api\CustomerManagementInterface;
use Aheadworks\RequisitionLists\Api\RequisitionListRepositoryInterface;
use Aheadworks\RequisitionLists\Controller\RList\Index as IndexRList;
use Aheadworks\RequisitionLists\Model\RequisitionList\Provider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends IndexRList
{
    private const NO_ROUTE_CONFIG = 'web/default/no_route';
    /** @var CompanyUserManagementInterface  */
    private CompanyUserManagementInterface $companyUserManagement;
    /** @var ScopeConfigInterface  */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param Provider $provider
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param CustomerManagementInterface $customerManagement
     * @param PageFactory $pageFactory
     * @param ResponseFactory $responseFactory
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Provider $provider,
        Context $context,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        CustomerManagementInterface $customerManagement,
        PageFactory $pageFactory,
        ResponseFactory $responseFactory,
        RequisitionListRepositoryInterface $requisitionListRepository,
        CompanyUserManagementInterface $companyUserManagement,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $provider,
            $context,
            $storeManager,
            $customerSession,
            $customerManagement,
            $pageFactory,
            $responseFactory,
            $requisitionListRepository
        );
        $this->companyUserManagement = $companyUserManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param IndexRList $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface|Redirect
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function aroundDispatch(IndexRList $subject, callable $proceed, RequestInterface $request)
    {
        if (!$this->companyUserManagement->getCurrentUser()) {
            $resultRedirect = $subject->resultRedirectFactory->create();

            $noRoutePage = $this->scopeConfig->getValue(self::NO_ROUTE_CONFIG, ScopeInterface::SCOPE_STORE);
            $resultRedirect->setPath($noRoutePage);

            return $resultRedirect;
        }

        return parent::dispatch($request);
    }
}
