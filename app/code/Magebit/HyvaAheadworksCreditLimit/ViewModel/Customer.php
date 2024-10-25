<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCreditLimit package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCreditLimit
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCreditLimit\ViewModel;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Customer implements ArgumentInterface
{
    /** @var Session */
    private Session $customerSession;

    /**
     * Customer constructor.
     *
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * Get current customer ID
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->customerSession->getId() ? (int)$this->customerSession->getId() : null;
    }
}
