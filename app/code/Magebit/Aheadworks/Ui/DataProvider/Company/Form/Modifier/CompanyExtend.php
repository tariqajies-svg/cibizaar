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

namespace Magebit\Aheadworks\Ui\DataProvider\Company\Form\Modifier;

use Magebit\Aheadworks\Api\CompanyExtendRepositoryInterface;
use Magebit\Aheadworks\ViewModel\Ca\Company\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CompanyExtend implements ModifierInterface
{
    /**
     * @param CompanyExtendRepositoryInterface $companyExtendRepository
     * @param Form $formViewModel
     */
    public function __construct(
        private readonly CompanyExtendRepositoryInterface $companyExtendRepository,
        private readonly Form $formViewModel
    ) {
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        if (!isset($data['company'])) {
            return $data;
        }

        $companyId = (int) $data['company']['id'];
        $extendedCompany = $this->companyExtendRepository->getByCompanyId($companyId);

        if ($extendedCompany && $extendedCompany->getId()) {
            $descriptions = array_map(function ($item) {
                return $item['value'];
            }, $this->formViewModel->getCompanyDescriptionOptions());

            $data['company_extend'] = [
                'tln' => $extendedCompany->getTln(),
                'website' => $extendedCompany->getWebsite()
            ];

            if (!in_array($extendedCompany->getDescription(), $descriptions)) {
                $data['company_extend']['description'] = __('Other');
                $data['company_extend']['description_other'] = $extendedCompany->getDescription();
            } else {
                $data['company_extend']['description'] = $extendedCompany->getDescription();
            }
        } else {
            // Case for old companies that have not yet established this data
            $data['company_extend'] = [
                'tln' => '',
                'website' => ''
            ];
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
