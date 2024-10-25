<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\ViewModel;

use Hyva\Theme\ViewModel\StoreSwitcher as HyvaStoreSwitcher;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreSwitcher extends HyvaStoreSwitcher
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param EncoderInterface $encoder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        private readonly EncoderInterface $encoder,
        private readonly UrlInterface $urlBuilder
    ) {
        parent::__construct($storeManager, $scopeConfig);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRawStoresArray(): array
    {
        $stores = array_merge(...$this->getRawStores());
        $storesArray = [];

        foreach ($stores as $store) {
            $storesArray[$store->getGroupId()][$store->getId()] = [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'locale' => $store->getLocaleCode(),
                'redirect' => $this->urlBuilder->getUrl(
                    'stores/store/redirect',
                    [
                        '___store' => $store->getCode(),
                        '___from_store' => $this->storeManager->getStore()->getCode(),
                        ActionInterface::PARAM_NAME_URL_ENCODED => $this->encoder->encode(
                            $store->getCurrentUrl(false)
                        ),
                    ]
                )
            ];
        }

        return $storesArray;
    }
}
