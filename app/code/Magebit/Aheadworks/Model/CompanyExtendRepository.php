<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Aheadworks
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Aheadworks\Model;

use Exception;
use Magebit\Aheadworks\Api\CompanyExtendRepositoryInterface;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magebit\Aheadworks\Model\ResourceModel\CompanyExtendModel\CompanyExtendCollectionFactory;
use Magebit\Aheadworks\Model\ResourceModel\CompanyExtendResource;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;

class CompanyExtendRepository implements CompanyExtendRepositoryInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CompanyExtendResource $companyExtendResource
     * @param SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param CompanyExtendCollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CompanyExtendResource $companyExtendResource,
        private readonly SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        private readonly CompanyExtendCollectionFactory $collectionFactory
    ) {
    }

    /**
     * @param CompanyExtendInterface $companyExtend
     * @return CompanyExtendInterface
     * @throws CouldNotSaveException
     */
    public function save(CompanyExtendInterface $companyExtend): CompanyExtendInterface
    {
        try {
            $this->companyExtendResource->save($companyExtend);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $companyExtend;
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @return array
     */
    public function getList(SearchCriteria $searchCriteria): array
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        return $collection->getItems();
    }

    /**
     * @param int $companyId
     * @return CompanyExtendInterface|null
     */
    public function getByCompanyId(int $companyId): ?CompanyExtendInterface
    {
        return current($this->getList(
            $this->searchCriteriaBuilder->addFilter(CompanyExtendInterface::COMPANY_ID, $companyId)
            ->create()
        )) ?: null;
    }
}
