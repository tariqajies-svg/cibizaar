<?php
/**
 * This file is part of the Magebit_Checkout package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Checkout\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class CheckoutCollectRates implements ObserverInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly Session $checkoutSession,
        protected readonly CartRepositoryInterface $quoteRepository
    ) {
    }

    /**
     * Collect shipping rates when before checkout page is loaded.
     * This is necessary because collector is disabled on addToCart event.
     *
     * @see \Magebit\Catalog\Plugin\AroundCollectRates::aroundCollectRates
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if ($this->request->getFullActionName() !== 'checkout_index_index') {
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        $quote->collectTotals();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $this->quoteRepository->save($quote);
    }
}
