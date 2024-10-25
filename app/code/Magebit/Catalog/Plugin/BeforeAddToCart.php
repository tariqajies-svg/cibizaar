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

use Magento\Checkout\Controller\Cart\Add;
use Magento\Customer\Model\Session;

class BeforeAddToCart
{
    public const IS_ADDING_TO_CART = 'is_adding_to_cart';

    public function __construct(
        private readonly Session $customerSession
    ) {
    }

    /**
     * @param Add $subject
     * @return array
     */
    public function beforeExecute(Add $subject): array
    {
        if ($this->customerSession->isLoggedIn()) {
            $this->customerSession->setData(self::IS_ADDING_TO_CART, true);
        }

        return [];
    }
}
