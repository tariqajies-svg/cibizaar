<?php
/**
 * This file is part of the Magebit_TwoFa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_TwoFa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\TwoFa\Controller\Ajax;

use Exception;
use Magebit\TwoFa\Service\TwoFaService;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Ajax\Login as LoginBase;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class Login extends LoginBase
{
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param TwoFaService $twoFaService
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helper,
        AccountManagementInterface $customerAccountManagement,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        private readonly TwoFaService $twoFaService,
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $helper,
            $customerAccountManagement,
            $resultJsonFactory,
            $resultRawFactory,
            $cookieManager,
            $cookieMetadataFactory
        );
    }

    /**
     * @return Json|(Json&ResultInterface)|Raw|ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $httpBadRequestCode = 400;

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {
            $customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
//            if ($this->twoFaService->shouldBypassTwoFa($customer)) {
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                    $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                }
                if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                    $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                    $this->getAccountRedirect()->clearRedirectCookie();
                }
//            } else {
//                return $this->resultFactory->create(ResultFactory::TYPE_JSON)
//                    ->setData([
//                        'success' => true,
//                        'email' => $customer->getEmail(),
//                        'allowedMethods' => $this->twoFaService->getCustomerMethodsCodes($customer)
//                    ]);
//            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return  $this->_redirect($this->_redirect->getRefererUrl());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return  $this->_redirect($this->_redirect->getRefererUrl());
        }
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }
}
