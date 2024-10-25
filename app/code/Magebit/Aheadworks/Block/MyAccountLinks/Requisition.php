<?php
/**
 * This file is part of the Magebit_Aheadworks package.
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

namespace Magebit\Aheadworks\Block\MyAccountLinks;

use Aheadworks\Ca\Api\CompanyUserManagementInterface;
use Aheadworks\RequisitionLists\Api\CustomerManagementInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Requisition extends MyAccountLink
{
    /**
     * @var CompanyUserManagementInterface
     */
    private CompanyUserManagementInterface $companyUserManagement;

    /**
     * @param TemplateContext $context
     * @param DefaultPathInterface $defaultPath
     * @param HttpContext $httpContext
     * @param CustomerManagementInterface $customerManagement
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        DefaultPathInterface $defaultPath,
        HttpContext $httpContext,
        CustomerManagementInterface $customerManagement,
        CompanyUserManagementInterface $companyUserManagement,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $httpContext, $customerManagement, $data);
        $this->companyUserManagement = $companyUserManagement;
    }

    /**
     * Hiding link for non-company users
     * @return string
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $isActive = $this->companyUserManagement->getCurrentUser();

        if (!$isActive) {
            return '';
        }

        return parent::_toHtml();
    }
}
