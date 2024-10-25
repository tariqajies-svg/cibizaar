<?php
/**
 * This file is part of the Magebit_HyvaAheadworksQuickOrder package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksQuickOrder
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksQuickOrder\ViewModel;

use Aheadworks\QuickOrder\Model\Config as ModuleConfig;
use Aheadworks\QuickOrder\Model\Url;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ArgumentInterface
{
    /**
     * Config configuration
     *
     * @param Url $url
     * @param UrlInterface $urlInterface
     * @param ModuleConfig $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Url $url,
        private readonly UrlInterface $urlInterface,
        private readonly ModuleConfig $config,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Get config for quick orders page
     *
     * @return array
     * @throws LocalizedException
     */
    public function getConfig(): array
    {
        $websiteId = (int) $this->storeManager->getWebsite()->getId();

        return [
            'updateQtyUrl' => $this->url->getUpdateItemQtyUrl(),
            'removeItemUrl' => $this->url->getRemoveItemUrl(),
            'configureUrl' => $this->urlInterface->getUrl('hyva_aw_quick_order/quickOrders/configureItem'),
            'updateItemOptionUrl' => $this->url->getUpdateItemOptionUrl(),
            'isAddToListButtonDisplayed' => $this->config->isAddToListButtonDisplayed($websiteId),
            'isQtyInputDisplayed' => $this->config->isQtyInputDisplayed($websiteId)
        ];
    }
}
