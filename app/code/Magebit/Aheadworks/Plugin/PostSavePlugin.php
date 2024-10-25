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

namespace Magebit\Aheadworks\Plugin;

use Aheadworks\Ca\Controller\Adminhtml\Company\Save;
use Aheadworks\Ca\Controller\Company\SavePost;
use Magebit\Aheadworks\Api\CompanyExtendRepositoryInterface;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;

class PostSavePlugin
{
    /**
     * @param CompanyExtendRepositoryInterface $companyExtendRepository
     * @param RequestInterface $request
     * @param DataObjectHelper $dataObjectHelper
     * @param CompanyExtendInterfaceFactory $companyExtendInterfaceFactory
     */
    public function __construct(
        private readonly CompanyExtendRepositoryInterface $companyExtendRepository,
        private readonly RequestInterface $request,
        private readonly DataObjectHelper $dataObjectHelper,
        private readonly CompanyExtendInterfaceFactory $companyExtendInterfaceFactory
    ) {
    }

    /**
     * @param SavePost|Save $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(SavePost|Save $subject, $result)
    {
        $company = $this->request->getParam('company', []);

        if (!isset($company['id'])) {
            return $result;
        }

        $extendedCompany = $this->companyExtendRepository->getByCompanyId((int)$company['id']);

        if (!$extendedCompany) {
            // Case for old companies that have not yet established this data
            $extendedCompany = $this->companyExtendInterfaceFactory->create();
            $extendedCompany->setCompanyId((int)$company['id']);
        }

        $extendedData = $this->request->getParam('company_extend', []);
        $this->dataObjectHelper->populateWithArray(
            $extendedCompany,
            $extendedData,
            CompanyExtendInterface::class
        );

        if (isset($extendedData['description_other']) && $extendedData['description'] === __('Other')->render()) {
            $extendedCompany->setDescription($extendedData['description_other']);
        }

        $this->companyExtendRepository->save($extendedCompany);

        return $result;
    }
}
