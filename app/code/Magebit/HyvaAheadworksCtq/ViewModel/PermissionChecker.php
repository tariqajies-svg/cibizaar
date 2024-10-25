<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCtq package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCreditLimit
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCtq\ViewModel;

use Aheadworks\Ca\Api\AuthorizationManagementInterface;
use Aheadworks\Ctq\Model\Quote\Permission\Checker;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template\Context;

class PermissionChecker extends DataObject implements ArgumentInterface
{
    /**
     * @param Checker $checker
     * @param Context $context
     * @param Session $session
     * @param AuthorizationManagementInterface $authorizationManagement
     * @param array $data
     */
    public function __construct(
        protected readonly Checker $checker,
        protected readonly Context $context,
        protected readonly Session $session,
        protected readonly AuthorizationManagementInterface $authorizationManagement,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isQuoteAllowed(): bool
    {
        $customerId = $this->session->getCustomer()->getId();
        $storeId = $this->context->getStoreManager()->getStore()->getId();

        return $this->checker->check($customerId, $storeId);
    }

    /**
     * @return bool
     */
    public function isCurrentUserAllowedQuote()
    {
        return $this->authorizationManagement->isAllowedByResource('Aheadworks_Ctq::company_quotes_allow_using');
    }
}
