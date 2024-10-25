<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Plugin;

use Magento\Customer\Model\Session;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class AroundCollectRates
{
    /**
     * @param Session $customerSession
     */
    public function __construct(
        private readonly Session $customerSession
    ) {
    }

    /**
     * Don't collect rates if we are
     *
     * @param Shipping $subject
     * @param callable $proceed
     * @param RateRequest $request
     * @return Shipping
     */
    public function aroundCollectRates(Shipping $subject, callable $proceed, RateRequest $request)
    {
        if ($this->customerSession->isLoggedIn() && $this->customerSession->getData(BeforeAddToCart::IS_ADDING_TO_CART)) {
            $this->customerSession->setData(BeforeAddToCart::IS_ADDING_TO_CART, false);
            return $subject;
        }

        return $proceed($request);
    }
}
