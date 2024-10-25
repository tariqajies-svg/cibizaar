<?php

namespace Magebit\HyvaAheadworksCa\Service;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GetParentCustomerByChild
{
    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Returns parent customer if provided customer has it otherwise returns null.
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerInterface|null
     */
    public function execute(CustomerInterface $customer): ?CustomerInterface
    {
        if (!$customer->getId()) {
            return null;
        }
        $parentIdAttribute = $customer->getCustomAttribute('parent_id');
        if ($parentIdAttribute && $parentIdAttribute->getValue()) {
            try {
                return $this->customerRepository->getById((int)$parentIdAttribute->getValue());
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        return null;
    }
}
