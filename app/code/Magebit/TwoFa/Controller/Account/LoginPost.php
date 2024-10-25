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

namespace Magebit\TwoFa\Controller\Account;

use Exception;
use Magebit\TwoFa\Service\TwoFaService;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\Account\LoginPost as LoginPostBase;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;

class LoginPost extends LoginPostBase
{
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param TwoFaService $twoFaService
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface
        $customerAccountManagement,
        private readonly CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        private readonly TwoFaService $twoFaService,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $customerSession, $customerAccountManagement, $customerHelperData, $formKeyValidator, $accountRedirect);
    }

    /**
     * @return Forward|Json|(Json&ResultInterface)|Redirect
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);

//                    if ($this->twoFaService->shouldBypassTwoFa($customer)) {
                        $this->twoFaService->loginUser($customer);
                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            // URL is checked to be internal in $this->_redirect->success()
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            return $resultRedirect;
                        }
                        return $this->accountRedirect->getRedirect();
//                    }

                    return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                        ->setData([
                            'success' => true,
                            'email' => $customer->getEmail(),
                            'allowedMethods' => $this->twoFaService->getCustomerMethodsCodes($customer)
                        ]);
                } catch (EmailNotConfirmedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'confirmAccountErrorMessage',
                        ['url' => $this->customerHelperData->getEmailConfirmationUrl($login['username'])]
                    );
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}
