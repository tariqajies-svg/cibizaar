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

use Aheadworks\Ca\Api\Data\CompanyExtensionFactory;
use Aheadworks\Ca\Api\Data\CompanyInterface;
use Aheadworks\Ca\Model\Company\Address\Renderer;
use Magebit\Aheadworks\Api\Data\CompanyExtendInterfaceFactory;

class AddressRendererPlugin
{
    /**
     * @param CompanyExtensionFactory $companyExtensionFactory
     */
    public function __construct(
        private readonly CompanyExtensionFactory $companyExtensionFactory
    ) {
    }

    /**
     * @param Renderer $subject
     * @param CompanyInterface $company
     * @return array
     */
    public function beforeRenderAddressFromCompany(Renderer $subject, CompanyInterface $company): array
    {
        if ($company->getExtensionAttributes()) {
            $company->setExtensionAttributes($this->companyExtensionFactory->create());
        }

        return [$company];
    }
}
