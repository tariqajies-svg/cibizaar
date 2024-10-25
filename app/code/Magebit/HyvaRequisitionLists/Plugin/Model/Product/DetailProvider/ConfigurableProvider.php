<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
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
namespace Magebit\HyvaRequisitionLists\Plugin\Model\Product\DetailProvider;

use Aheadworks\RequisitionLists\Model\Config;
use Aheadworks\RequisitionLists\Model\Product\Checker\Inventory\Checker as InventoryChecker;
use Aheadworks\RequisitionLists\Model\Product\DetailProvider\ConfigurableProvider as ConfigurableProviderOrigin;
use Aheadworks\RequisitionLists\Model\RequisitionList\Item\Options\Converter as OptionConverter;
use Magento\Catalog\Block\Product\ImageFactory as ProductImageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ConfigurableProvider extends ConfigurableProviderOrigin
{
    // @codingStandardsIgnoreStart
    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductImageFactory $productImageFactory
     * @param OptionConverter $optionConverter
     * @param InventoryChecker $inventoryChecker
     * @param Config $config
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductImageFactory $productImageFactory,
        OptionConverter $optionConverter,
        InventoryChecker $inventoryChecker,
        private readonly Config $config
    ) {
        parent::__construct($storeManager, $productImageFactory, $optionConverter, $inventoryChecker, $config);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Retrieve prepared product image url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductImageUrl(): string
    {
        if ($this->config->isUseParentImageForConfigurable($this->storeManager->getStore()->getId())
            && $this->parentProduct
        ) {
            $product = $this->parentProduct;
        } else {
            $product = $this->product;
        }

        $productImageBlock = $this->productImageFactory->create(
            $product,
            'product_page_image_small',
            []
        );

        return $productImageBlock->getImageUrl();
    }

    /**
     * Get order options
     *
     * @return array
     */
    protected function getOrderOptions(): array
    {
        $options['info_buyRequest'] = [];
        if (!$this->getIsError()) {
            $type = $this->parentProduct->getTypeInstance();
            $options = $type->getOrderOptions($this->parentProduct);
        }

        return $options;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    private function isParentProductAvailableForSite(): bool
    {
        return $this->parentProduct && $this->isProductInWebsite($this->parentProduct);
    }

    /**
     * Check if parent product are enabled and in stock
     *
     * @return bool
     */
    private function isParentAvailableForSale(): bool
    {
        if ($this->parentProduct && ($this->parentProduct->isDisabled() || !$this->parentProduct->isSalable())) {
            return false;
        }

        return true;
    }
}
