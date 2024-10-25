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

namespace Magebit\HyvaAheadworksQuickOrder\Service;

use Aheadworks\QuickOrder\Api\Data\ProductListItemInterface;
use Aheadworks\QuickOrder\Api\ProductListItemRepositoryInterface;
use Aheadworks\QuickOrder\Model\Product\View\DataApplier;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;

class ConfigurableItem
{
    private const BASE_BLOCK = 'product.info.options.configurable';

    /**
     * ConfigurableItem constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param DataApplier $productViewDataApplier
     * @param PageFactory $resultPageFactory
     * @param ProductListItemRepositoryInterface $productListItemRepository
     * @param ProductRepositoryInterface $productRepository
     * @param SwatchHelper $swatchHelper
     */
    public function __construct(
        private readonly StoreManagerInterface              $storeManager,
        private readonly DataApplier                        $productViewDataApplier,
        private readonly PageFactory                        $resultPageFactory,
        private readonly ProductListItemRepositoryInterface $productListItemRepository,
        private readonly ProductRepositoryInterface         $productRepository,
        private readonly SwatchHelper                       $swatchHelper
    ) {
    }

    /**
     * Get data necessary for QO configurable item popup
     *
     * @param string $itemKey
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(string $itemKey): array
    {
        $storeId = $this->storeManager->getStore()->getId();
        $item = $this->productListItemRepository->getByKey($itemKey);
        $product = $this->productRepository->getById($item->getProductId(), false, $storeId);

        $block = $this->getBlock($product, $item);

        if ($block) {
            $attributes = $block->decorateArray($block->getAllowAttributes());
            $attributeData = [];

            foreach ($attributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeData[] = [
                    'id' => $attribute->getAttributeId(),
                    'isSwatch' => $this->swatchHelper->isSwatchAttribute($productAttribute),
                    'productAttribute' => [
                        'code' => $productAttribute->getAttributeCode(),
                        'label' => $productAttribute->getStoreLabel(),

                    ]
                ];
            }

            return [
                'title' => __('Configure %1', $product->getName()),
                'productId' => $product->getId(),
                'attributes' => $attributeData,
                'jsonConfig' => $block->getJsonConfig(),
                'jsonSwatchConfig' => $block->getJsonSwatchConfig()
            ];
        }

        return [];
    }

    /**
     * Get configurable product option block
     *
     * @param ProductInterface|Product $product
     * @param ProductListItemInterface $item
     * @return false|Configurable
     */
    private function getBlock(ProductInterface|Product $product, ProductListItemInterface $item): ?Configurable
    {
        $this->productViewDataApplier->apply($product, $item);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('catalog_product_view');
        $resultPage->addHandle('catalog_product_view_type_' . $product->getTypeId());

        return $resultPage->getLayout()->getBlock(self::BASE_BLOCK) ?: null;
    }
}
