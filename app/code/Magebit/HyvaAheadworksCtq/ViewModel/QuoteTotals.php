<?php
/**
 * This file is part of the MagebitB2B_HyvaAheadWorksCtq package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaAheadworksCtq\ViewModel;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class QuoteTotals implements ArgumentInterface
{
    private Currency $currencyCode;

    /**
     * QuoteTotals constructor.
     *
     * @param StoreManagerInterface $storeConfig
     * @param CurrencyFactory $currencyFactory
     */
    public function __construct(
        private readonly StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory
    ) {
        $this->currencyCode = $currencyFactory->create();
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getSymbol():? string
    {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }
}
