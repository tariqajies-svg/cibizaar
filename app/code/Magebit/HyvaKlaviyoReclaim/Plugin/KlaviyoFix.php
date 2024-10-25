<?php
/**
 * This file is part of the Magebit_HyvaKlaviyoReclaim package.
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\HyvaKlaviyoReclaim\Plugin;

use Klaviyo\Reclaim\Observer\SalesQuoteProductAddAfter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Class KlaviyoFix
 * Fix stripslashes of null throwing error
 */
class KlaviyoFix
{
    /**
     * @param SalesQuoteProductAddAfter $subject
     * @param Quote $quote
     * @param Item $addedItem
     * @return void
     */
    public function beforeKlBuildCartData(SalesQuoteProductAddAfter $subject, Quote $quote, Item $addedItem): void
    {
        $cartItems = $quote->getAllVisibleItems() ?? [];
        foreach ($cartItems as $item) {
            $product = $item->getProduct();
            $product->setData('small_image', $product->getData('small_image') ?? '');
        }
    }

    /**
     * @param SalesQuoteProductAddAfter $subject
     * @param Quote $quote
     * @param Item $addedItem
     * @return void
     */
    public function beforeKlAddedToCartItemData(SalesQuoteProductAddAfter $subject, Quote $quote, Item $addedItem): void
    {
        $product = $addedItem->getProduct();
        $product->setData('small_image', $product->getData('small_image') ?? '');
    }
}
