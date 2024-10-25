<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\Aheadworks\Plugin\Ctq\Controller;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\Ctq\Controller\Quote\Index as IndexCtq;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Index extends IndexCtq
{
    private const NO_ROUTE_CONFIG = 'web/default/no_route';
    /** @var CompanyUserManagementInterface  */
    private CompanyUserManagementInterface $companyUserManagement;
    /** @var ScopeConfigInterface  */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        CompanyUserManagementInterface $companyUserManagement,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession);
        $this->companyUserManagement = $companyUserManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param IndexCtq $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface|Redirect
     * @throws NotFoundException
     */
    public function aroundDispatch(IndexCtq $subject, callable $proceed, RequestInterface $request)
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
