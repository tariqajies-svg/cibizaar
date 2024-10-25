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

use Aheadworks\Ctq\Block\Account\Link;
use Aheadworks\Ctq\Model\Quote\Permission\Checker as PermissionChecker;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Ca\Api\CompanyUserManagementInterface;

class MyQuotes extends Link implements SortLinkInterface
{
    /** @var CompanyUserManagementInterface  */
    private CompanyUserManagementInterface $companyUserManagement;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param CustomerSession $customerSession
     * @param PermissionChecker $permissionChecker
     * @param CompanyUserManagementInterface $companyUserManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        PermissionChecker $permissionChecker,
        CompanyUserManagementInterface $companyUserManagement,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $customerSession, $permissionChecker, $data);
        $this->companyUserManagement = $companyUserManagement;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _toHtml(): string
    {
        $isActive = $this->companyUserManagement->getCurrentUser();

        if (!$isActive) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return array|int|mixed|null
     */
    public function getSortOrder()
    {
        return $this->getData('sortOrder');
    }
}
