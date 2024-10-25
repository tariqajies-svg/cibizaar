<?php
/**
 * This file is part of the Magebit_Customer package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Customer
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Customer\Controller\Account;

use Magebit\Aheadworks\Service\CompanySaveService;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;

class Register implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param CreatePost $customerSave
     * @param CompanySaveService $companySaveService
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly CreatePost $customerSave,
        private readonly CompanySaveService $companySaveService,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly ManagerInterface $messageManager
    ) {
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $isCompany = $this->request->getParam('is_company_account');

        try {
            if ($isCompany) {
                $this->companySaveService->saveCompany($this->request);

                return $this->resultRedirectFactory->create()->setPath('customer/account/index');
            }
            return $this->customerSave->execute();
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('customer/account/create');
    }
}
