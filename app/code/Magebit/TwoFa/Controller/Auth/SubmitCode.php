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

namespace Magebit\TwoFa\Controller\Auth;

use Exception;
use Magebit\TwoFa\Service\TwoFaService;
use Magento\Customer\Model\Account\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class SubmitCode implements HttpPostActionInterface
{
    /**
     * @param TwoFaService $twoFaService
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Redirect $accountRedirect
     * @param RedirectInterface $redirect
     */
    public function __construct(
        private readonly TwoFaService $twoFaService,
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Redirect $accountRedirect,
        private readonly RedirectInterface $redirect
    ) {
    }

    /**
     * @return ResponseInterface|Forward|Json|(Json&ResultInterface)|\Magento\Framework\Controller\Result\Redirect|(\Magento\Framework\Controller\Result\Redirect&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        $method = $this->request->getParam('method');
        $email = $this->request->getParam('email');
        $code = $this->request->getParam('code');
        $redirectOnSuccess = $this->request->getParam('redirectOnSuccess');

        try {
            if ($this->twoFaService->validateCode($code, $method, $email)) {
                $this->twoFaService->loginUser($this->twoFaService->getCustomerByEmail($email));

                if ($redirectOnSuccess) {
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($redirectOnSuccess);
                }

                $redirectUrl = $this->accountRedirect->getRedirectCookie();
                if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                    $this->accountRedirect->clearRedirectCookie();
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    // URL is checked to be internal in $this->_redirect->success()
                    $resultRedirect->setUrl($this->redirect->success($redirectUrl));
                    return $resultRedirect;
                }
                return $this->accountRedirect->getRedirect();
            } else {
                return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                    'success' => false,
                    'message' => __('Invalid confirmation code')
                ]);
            }
        } catch (Exception $exception) {
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }
    }
}
