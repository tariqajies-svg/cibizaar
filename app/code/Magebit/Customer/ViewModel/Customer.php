<?php
/**
 * This file is part of the Magebit_Customer package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\Customer\ViewModel;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Customer implements ArgumentInterface
{
    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $session
     */
    public function __construct(
        protected readonly CustomerRepositoryInterface $customerRepository,
        protected readonly Session $session
    ) {
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCurrentCustomer(): ?CustomerInterface
    {
        try {
            return $this->customerRepository->getById($this->session->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return null;
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
