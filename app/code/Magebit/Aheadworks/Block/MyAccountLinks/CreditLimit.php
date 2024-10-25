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
use Aheadworks\CreditLimit\Api\CustomerManagementInterface;
use Aheadworks\CreditLimit\Block\Customer\Link;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;

class CreditLimit extends Link implements SortLinkInterface
{
    /** @var CompanyUserManagementInterface */
    private CompanyUserManagementInterface $companyUserManagement;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        CustomerManagementInterface $customerManagement,
        CompanyUserManagementInterface $companyUserManagement,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $customerSession, $customerManagement, $data);
        $this->companyUserManagement = $companyUserManagement;
    }

    /**
     * @return string
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
