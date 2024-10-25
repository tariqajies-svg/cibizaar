<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCa\ViewModel\Role;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\Ca\Api\Data\RoleInterface;
use Aheadworks\Ca\Api\RoleRepositoryInterface;
use Aheadworks\Ca\ViewModel\ListViewModelInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model for Role list
 */
class RoleList implements ArgumentInterface, ListViewModelInterface
{
    private const PAGE_SIZE = 999;

    /**
     * @var RoleRepositoryInterface
     */
    private RoleRepositoryInterface $roleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchResultsInterface|null
     */
    private ?SearchResultsInterface $roleSearchResults = null;

    /**
     * @var CompanyUserManagementInterface
     */
    private CompanyUserManagementInterface $companyUserManagement;

    /**
     * @param RoleRepositoryInterface $roleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param CompanyUserManagementInterface $companyUserManagement
     */
    public function __construct(
        RoleRepositoryInterface $roleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CompanyUserManagementInterface $companyUserManagement
    ) {
        $this->roleRepository = $roleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->companyUserManagement = $companyUserManagement;
    }

    /**
     * Retrieves search criteria builder
     *
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder(): SearchCriteriaBuilder
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * Retrieve role search results
     *
     * @return SearchResultsInterface|null
     * @throws LocalizedException
     */
    public function getSearchResults(): ?SearchResultsInterface
    {
        if ($this->roleSearchResults === null) {
            $companyUser = $this->companyUserManagement->getCurrentUser();
            $companyId = $companyUser->getExtensionAttributes()->getAwCaCompanyUser()->getCompanyId();

            $sortOrder = $this->sortOrderBuilder
                ->setField(RoleInterface::ID)
                ->setDirection(SortOrder::SORT_ASC)
                ->create();

            $this->searchCriteriaBuilder
                ->addFilter(RoleInterface::COMPANY_ID, ['eq' => $companyId])
                ->setPageSize(self::PAGE_SIZE)
                ->addSortOrder($sortOrder);

            $this->roleSearchResults = $this->roleRepository->getList($this->searchCriteriaBuilder->create());
        }

        return $this->roleSearchResults;
    }
}
