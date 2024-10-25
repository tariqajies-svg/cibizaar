<?php
/**
 * This file is part of the Magebit_PaymentFee package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\PaymentFee\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class PaymentConfigProvider implements ConfigProviderInterface
{
    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        private readonly Session $checkoutSession
    ) {
    }

    /**
     * Add selected payment method checkout config from quote
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        $quote = $this->checkoutSession->getQuote();

        $additionalVariables = [];
        if ($quote->getId()) {
            $additionalVariables['selectedPaymentMethod'] = $quote->getPayment()->getMethod();
        }
        return $additionalVariables;
    }
}
