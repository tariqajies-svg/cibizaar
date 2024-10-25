<?php
/**
 * This file is part of the Magebit_GeoIPCurrency package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\GeoIPCurrency\Observer\Frontend\Controller;

use Magebit\GeoIPCurrency\Service\SetCurrencyBasedOnGeoIPCountry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ActionPredispatch implements ObserverInterface
{
    /**
     * @param SetCurrencyBasedOnGeoIPCountry $setCurrencyBasedOnGeoIPCountry
     */
    public function __construct(
        protected readonly SetCurrencyBasedOnGeoIPCountry $setCurrencyBasedOnGeoIPCountry
    ) {
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        $this->setCurrencyBasedOnGeoIPCountry->execute();
    }
}
