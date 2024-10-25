<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaRequisitionLists\ViewModel;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\Ca\Model\ThirdPartyModule\Aheadworks\RequisitionLists\Model\JoinProcessor;
use Aheadworks\Ca\Model\ThirdPartyModule\Aheadworks\RequisitionLists\Model\RequisitionListPermission;
use Aheadworks\RequisitionLists\Api\Data\RequisitionListInterface;
use Aheadworks\RequisitionLists\Model\RequisitionListRepository;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Collection as RequisitionListCollection;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Grid\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;

class RequisitionList extends Template
{
    /**
     * @param FilterBuilder $filterBuilder
     * @param FilterGroup $filterGroup
     * @param CustomerSession $customerSession
     * @param SearchCriteriaInterface $searchCriteria
     * @param RequisitionListRepository $requisitionListRepository
     * @param CollectionFactory $requisitionListCollectionFactory
     * @param JoinProcessor $joinProcessor
     * @param DateTimeFactory $dateTimeFactory
     * @param Context $context
     * @param RequisitionListPermission $requisitionListPermission
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        private readonly FilterBuilder $filterBuilder,
        private readonly FilterGroup $filterGroup,
        private readonly CustomerSession $customerSession,
        private readonly SearchCriteriaInterface $searchCriteria,
        private readonly RequisitionListRepository $requisitionListRepository,
        private readonly CollectionFactory $requisitionListCollectionFactory,
        private readonly JoinProcessor $joinProcessor,
        private readonly DateTimeFactory $dateTimeFactory,
        Template\Context $context,
        private readonly RequisitionListPermission $requisitionListPermission,
        private readonly CompanyUserManagementInterface $companyUserManagement,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return RequisitionListInterface[]|array
     */
    public function getSearchResults(): array
    {
        if ($customerId = $this->customerSession->getCustomerId()) {
            $filter = $this->filterBuilder
                ->setField(OrderInterface::CUSTOMER_ID)
                ->setValue($customerId)
                ->setConditionType('eq')->create();

            $filterGroup = $this->filterGroup->setFilters([$filter]);
            $searchCriteria = $this->searchCriteria->setFilterGroups([$filterGroup]);

            return $this->requisitionListRepository->getList($searchCriteria)->getItems();
        }

        return [];
    }

    /**
     * Get requisition lists joined with 'aw_ca_company_requisition_list' table
     *
     * @return RequisitionListCollection|null
     * @throws LocalizedException
     */
    public function getRequisitionListCollection()
    {
        $currentUserId = $this->customerSession->getCustomerId();
        if ($currentUserId) {
            $companyUser = $this->companyUserManagement->getCurrentUser();
            $companyId = $companyUser->getExtensionAttributes()->getAwCaCompanyUser()->getCompanyId();

            /** @var RequisitionListCollection $collection */
            $collection = $this->requisitionListCollectionFactory->create();
            if ($companyUser && $this->companyUserManagement->getRootUserForCompany($companyId)->getId() === $currentUserId) {
                $userIds = $this->getCompanyUsersIds();
            } else {
                $userIds = [$currentUserId];
            }

            $collection->addFieldToFilter(OrderInterface::CUSTOMER_ID, ['in' => $userIds]);

            if (!$this->requisitionListPermission->isCustomerHasCompanyPermissions()) {
                $this->joinProcessor->joinColumns($collection);
            }

            return $collection->setOrder('updated_at', 'desc');
        }

        return null;
    }

    /**
     * @param $date
     * @return string
     */
    public function getFormatDate($date): string
    {
        $dateModel = $this->dateTimeFactory->create();
        return $dateModel->date('d/m/Y', $date);
    }

    /**
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById(int $productId): ProductInterface
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getCompanyUsersIds(): array
    {
        $result = [];
        $companyUser = $this->companyUserManagement->getCurrentUser();

        if (!$companyUser) {
            return $result;
        }

        $companyId = $companyUser->getExtensionAttributes()->getAwCaCompanyUser()->getCompanyId();

        $this->searchCriteriaBuilder
            ->addFilter('aw_ca_customer_by_company_id', $companyId)
            ->addFilter('aw_ca_customer_status_join', true);

        $customers = $this->customerRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        foreach ($customers as $customer) {
            $result[] = $customer->getId();
        }
        return $result;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
