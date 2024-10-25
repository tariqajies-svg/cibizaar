<?php
/**
 * This file is part of the Magebit_AheadworksCreditLimit package.
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

namespace Magebit\AheadworksCreditLimit\Rewrite\Aheadworks\CreditLimit\Model;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\Ca\Api\SellerCompanyManagementInterface;
use Aheadworks\CreditLimit\Api\Data\SummaryInterface;
use Aheadworks\CreditLimit\Api\Data\SummaryInterfaceFactory;
use Aheadworks\CreditLimit\Api\Data\SummarySearchResultsInterface;
use Aheadworks\CreditLimit\Api\Data\SummarySearchResultsInterfaceFactory;
use Aheadworks\CreditLimit\Model\CreditSummary;
use Aheadworks\CreditLimit\Model\CreditSummaryRepository as CreditSummaryRepositoryOriginal;
use Aheadworks\CreditLimit\Model\ResourceModel\CreditSummary as CreditSummaryResourceModel;
use Aheadworks\CreditLimit\Model\ResourceModel\Customer\Collection as CreditSummaryCollection;
use Aheadworks\CreditLimit\Model\ResourceModel\Customer\CollectionFactory as CreditSummaryCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

class CreditSummaryRepository extends CreditSummaryRepositoryOriginal
{
    /**
     * @var CreditSummaryResourceModel
     */
    private $resource;

    /**
     * @var SummaryInterfaceFactory
     */
    private $creditSummaryFactory;

    /**
     * @var CreditSummaryCollectionFactory
     */
    private $creditSummaryCollectionFactory;

    /**
     * @var SummarySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var array
     */
    private $registry = [];

    /**
     * @var array
     */
    private $registryByCustomerId = [];

    /**
     * @var SellerCompanyManagementInterface
     */
    private SellerCompanyManagementInterface $sellerCompanyManagement;

    /**
     * @var CompanyUserManagementInterface
     */
    private CompanyUserManagementInterface $companyUserManagement;

    /**
     * @param CreditSummaryResourceModel $resource
     * @param SummaryInterfaceFactory $creditSummaryFactory
     * @param CreditSummaryCollectionFactory $creditSummaryCollectionFactory
     * @param SummarySearchResultsInterfaceFactory $searchResultsFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param SellerCompanyManagementInterface $sellerCompanyManagement
     * @param CompanyUserManagementInterface $companyUserManagement
     */
    public function __construct(
        CreditSummaryResourceModel $resource,
        SummaryInterfaceFactory $creditSummaryFactory,
        CreditSummaryCollectionFactory $creditSummaryCollectionFactory,
        SummarySearchResultsInterfaceFactory $searchResultsFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        SellerCompanyManagementInterface $sellerCompanyManagement,
        CompanyUserManagementInterface $companyUserManagement
    ) {
        $this->resource = $resource;
        $this->creditSummaryFactory = $creditSummaryFactory;
        $this->creditSummaryCollectionFactory = $creditSummaryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->sellerCompanyManagement = $sellerCompanyManagement;
        $this->companyUserManagement = $companyUserManagement;
    }

    /**
     * Save credit limit summary
     *
     * @param SummaryInterface $creditSummary
     * @return SummaryInterface
     * @throws LocalizedException
     */
    public function save(SummaryInterface $creditSummary)
    {
        try {
            $this->resource->save($creditSummary);
            $this->registry[$creditSummary->getSummaryId()] = $creditSummary;
            $this->registryByCustomerId[$creditSummary->getCustomerId()] = $creditSummary;
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $creditSummary;
    }

    /**
     * Retrieve credit limit summary by customer ID
     *
     * @param int $customerId
     * @param bool $reload
     * @return SummaryInterface
     * @throws NoSuchEntityException
     */
    public function getByCustomerId($customerId, $reload = false)
    {
        if (!isset($this->registryByCustomerId[$customerId]) || $reload) {
            $creditSummaryData = $this->resource->loadByCustomerId($customerId);

            // Credit data is attached to a company administrator
            // If no credit data for current customer, try to get it for company admin
            if (!$creditSummaryData) {
                $company = $this->sellerCompanyManagement->getCompanyByCustomerId($customerId);
                if ($company) {
                    $companyAdmin = $this->companyUserManagement->getRootUserForCompany($company->getId());
                    $creditSummaryData = $this->resource->loadByCustomerId((int)$companyAdmin->getId());
                }
            }

            if (!$creditSummaryData) {
                throw NoSuchEntityException::singleField(SummaryInterface::CUSTOMER_ID, $customerId);
            }

            $creditSummary = $this->prepareDataObjectFromRowData($creditSummaryData);
            if ($creditSummary->getSummaryId()) {
                $this->registry[$creditSummary->getSummaryId()] = $creditSummary;
            }
            $this->registryByCustomerId[$customerId] = $creditSummary;
        }
        return $this->registryByCustomerId[$customerId];
    }

    /**
     * Retrieve credit limit summary items matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SummarySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CreditSummaryCollection $collection */
        $collection = $this->creditSummaryCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, SummaryInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SummarySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());

        $objects = [];
        /** @var SummaryInterface $creditSummary */
        foreach ($collection->getItems() as $item) {
            $objects[] = $this->prepareDataObjectFromModel($item);
        }
        $searchResults->setItems($objects);

        return $searchResults;
    }

    /**
     * Retrieves data object from model
     *
     * @param CreditSummary|DataObject|array $model
     * @return SummaryInterface
     */
    private function prepareDataObjectFromModel($model)
    {
        /** @var SummaryInterface $object */
        $object = $this->creditSummaryFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $object,
            is_array($model) ? $model : $model->getData(),
            SummaryInterface::class
        );

        return $object;
    }

    /**
     * Prepare data object from row data array
     *
     * @param array $dataArray
     * @return SummaryInterface
     */
    private function prepareDataObjectFromRowData($dataArray)
    {
        $notFormattedDataObject = $this->prepareDataObjectFromModel($dataArray);
        $formattedData = $this->dataObjectProcessor->buildOutputDataArray(
            $notFormattedDataObject,
            SummaryInterface::class
        );

        return $this->prepareDataObjectFromModel($formattedData);
    }
}
