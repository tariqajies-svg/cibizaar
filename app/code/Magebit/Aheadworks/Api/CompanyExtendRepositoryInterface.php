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

namespace Magebit\Aheadworks\Api;

use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magento\Framework\Api\SearchCriteria;

interface CompanyExtendRepositoryInterface
{
    /**
     * @param CompanyExtendInterface $companyExtend
     * @return CompanyExtendInterface
     */
    public function save(CompanyExtendInterface $companyExtend): CompanyExtendInterface;

    /**
     * @param SearchCriteria $searchCriteria
     * @return array
     */
    public function getList(SearchCriteria $searchCriteria): array;

    /**
     * @param int $companyId
     * @return CompanyExtendInterface|null
     */
    public function getByCompanyId(int $companyId): ?CompanyExtendInterface;
}
