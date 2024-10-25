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

use Aheadworks\Ca\Api\Data\CompanyInterface;
use Aheadworks\Ca\Model\CompanyRepository;
use Magebit\Aheadworks\Api\CompanyExtendRepositoryInterface;
use \Aheadworks\Ca\Api\Data\CompanyExtensionFactory;

class CompanyRepositoryPlugin
{
    /**
     * @param CompanyExtendRepositoryInterface $companyExtendRepository
     * @param CompanyExtensionFactory $companyExtensionFactory
     */
    public function __construct(
        private readonly CompanyExtendRepositoryInterface $companyExtendRepository,
        private readonly CompanyExtensionFactory $companyExtensionFactory
    ) {
    }

    /**
     * @param CompanyRepository $subject
     * @param int $companyId
     * @param bool $reload
     * @return array
     */
    public function afterGet(CompanyRepository $subject, CompanyInterface $company): CompanyInterface
    {
        $companyExtend = $this->companyExtendRepository->getByCompanyId((int) $company->getId());

        if ($companyExtend) {
            if (!($extensionAttributes = $company->getExtensionAttributes())) {
                $extensionAttributes = $this->companyExtensionFactory->create();
            }

            $extensionAttributes->setTln($companyExtend->getTln());
            $extensionAttributes->setWebsite($companyExtend->getWebsite());
            $extensionAttributes->setDescription($companyExtend->getDescription());

            $company->setExtensionAttributes($extensionAttributes);
        }

        return $company;
    }
}
