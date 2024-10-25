<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Bizaar\Plugin\Infibeam\Ccavenue\Model;

use Infibeam\Ccavenue\Model\Ccavenue as CcavenueAlias;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Ccavenue
{
    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        protected readonly CheckoutSession $checkoutSession,
    ) {
    }

    /**
     * We are restricting payment method by a quote currency.
     *
     * By default, method "canUseForCurrency" checks only for store view currency.
     *
     * @param CcavenueAlias $subject
     * @param bool $result
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterCanUseCheckout(CcavenueAlias $subject, bool $result): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteCurrency = $quote->getQuoteCurrencyCode();

        if (!$subject->canUseForCurrency($quoteCurrency)) {
            return false;
        }

        return $result;
    }
}
