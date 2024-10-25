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

namespace Magebit\HyvaRequisitionLists\Service;

use Aheadworks\RequisitionLists\Api\RequisitionListItemRepositoryInterface;
use Aheadworks\RequisitionLists\Model\Product\View\DataApplier;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Aheadworks\RequisitionLists\Api\Data\RequisitionListItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ConfigurableItem
{
    private const BASE_BLOCK = 'product.info.options.configurable';

    /**
     * ConfigurableItem constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param DataApplier $productViewDataApplier
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SwatchHelper $swatchHelper
     * @param RequisitionListItemRepositoryInterface $requisitionListItemRepository
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly DataApplier $productViewDataApplier,
        private readonly PageFactory $resultPageFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly SwatchHelper $swatchHelper,
        private readonly RequisitionListItemRepositoryInterface $requisitionListItemRepository
    ) {
    }

    /**
     * Get data necessary for RL configurable item popup
     *
     * @param string $itemId
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(string $itemId): array
    {
        $storeId = $this->storeManager->getStore()->getId();

        //list item id
        $item = $this->requisitionListItemRepository->get((int)$itemId);
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
     * @return false|Configurable
     */
    private function getBlock(ProductInterface $product, RequisitionListItemInterface $item): ?Configurable
    {
        $this->productViewDataApplier->apply($product, $item);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('catalog_product_view');
        $resultPage->addHandle('catalog_product_view_type_' . $product->getTypeId());

        return $resultPage->getLayout()->getBlock(self::BASE_BLOCK) ?: null;
    }
}
