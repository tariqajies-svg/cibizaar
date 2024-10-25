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

namespace Magebit\HyvaAheadworksCa\ViewModel;

use Magebit\HyvaAheadworksCa\Service\GetParentCustomerByChild;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CustomerData implements ArgumentInterface
{
    /** @var GetParentCustomerByChild */
    private GetParentCustomerByChild $getParentCustomerByChild;

    /**
     * @param GetParentCustomerByChild $getParentCustomerByChild
     */
    public function __construct(GetParentCustomerByChild $getParentCustomerByChild)
    {
        $this->getParentCustomerByChild = $getParentCustomerByChild;
    }

    /**
     * Returns email for display on frontend
     *
     * @param CustomerInterface $customer
     *
     * @return string
     */
    public function getEmailForDisplay(CustomerInterface $customer): string
    {
        $parentAccount = $this->getParentCustomerByChild->execute($customer);

        return $parentAccount ? $parentAccount->getEmail() : $customer->getEmail();
    }
}
