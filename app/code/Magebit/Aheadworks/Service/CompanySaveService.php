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

namespace Magebit\Aheadworks\Service;

use Aheadworks\Ca\Api\Data\CompanyInterface;
use Aheadworks\Ca\Api\SellerCompanyManagementInterface;
use Aheadworks\Ca\Controller\Company\DataProcessor;
use Aheadworks\Ca\Model\Source\Company\Status;
use Magebit\Aheadworks\Api\CompanyExtendRepositoryInterface;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;

class CompanySaveService
{
    /**
     * @param CustomerExtractor $customerExtractor
     * @param DataProcessor $companyDataProcessor
     * @param SellerCompanyManagementInterface $sellerCompanyManagement
     * @param Session $session
     * @param AccountManagementInterface $accountManagement
     * @param ManagerInterface $eventManager
     * @param CompanyExtendRepositoryInterface $companyExtendRepository
     * @param CompanyExtendInterfaceFactory $companyExtendInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly CustomerExtractor $customerExtractor,
        private readonly DataProcessor $companyDataProcessor,
        private readonly SellerCompanyManagementInterface $sellerCompanyManagement,
        private readonly Session $session,
        private readonly AccountManagementInterface $accountManagement,
        private readonly ManagerInterface $eventManager,
        private readonly CompanyExtendRepositoryInterface $companyExtendRepository,
        private readonly CompanyExtendInterfaceFactory $companyExtendInterfaceFactory,
        private readonly DataObjectHelper $dataObjectHelper,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return void
     * @throws InputException
     */
    public function saveCompany(RequestInterface $request): void
    {
        $customer = $this->customerExtractor->extract('customer_account_create', $request);
        $company = $this->companyDataProcessor->prepareCompany($request);
        $company->setStatus(Status::APPROVED);
        $company->setEmail($customer->getEmail());

        $customer = $this->createCustomer($customer, $request);

        $this->attachCaCustomerData($customer, $request);
        $company = $this->sellerCompanyManagement->createCompany($company, $customer);
        $this->saveExtended($request, $company);
    }

    /**
     * @param RequestInterface $request
     * @param CompanyInterface $company
     * @return void
     */
    public function saveExtended(RequestInterface $request, CompanyInterface $company): void
    {
        /** @var CompanyExtendInterface $companyExtend */
        $companyExtend = $this->companyExtendInterfaceFactory->create();
        $data = $request->getParam('company_extend', []);

        $this->dataObjectHelper->populateWithArray(
            $companyExtend,
            $data,
            CompanyExtendInterface::class
        );

        if (isset($data['description_other']) && $data['description'] === __('Other')->render()) {
            $companyExtend->setDescription($data['description_other']);
        }

        $companyExtend->setCompanyId($company->getId());

        $this->companyExtendRepository->save($companyExtend);
    }

    /**
     * @param CustomerInterface $customerData
     * @param RequestInterface $request
     * @return CustomerInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    private function createCustomer(CustomerInterface $customerData, RequestInterface $request): CustomerInterface
    {
        $password = $request->getParam('password');
        $confirmation = $request->getParam('password_confirmation');

        if ($password !== $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }

        $extensionAttributes = $customerData->getExtensionAttributes();
        $extensionAttributes->setIsSubscribed($request->getParam('is_subscribed', false));
        $customerData->setExtensionAttributes($extensionAttributes);
        $redirectUrl = $this->session->getBeforeAuthUrl();

        $customer = $this->accountManagement
            ->createAccount($customerData, $password, $redirectUrl);

        $this->customerRepository->save($customer);

        $this->eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );
        $this->session->setCustomerDataAsLoggedIn($customer);

        return $customer;
    }

    /**
     * @param CustomerInterface $customer
     * @param RequestInterface $request
     * @return void
     */
    private function attachCaCustomerData(CustomerInterface $customer, RequestInterface $request): void
    {
        $extensionAttributes = $request->getParam('extension_attributes');
        $this->dataObjectHelper->populateWithArray(
            $customer,
            ['extension_attributes' => $extensionAttributes],
            CustomerInterface::class
        );
    }
}
