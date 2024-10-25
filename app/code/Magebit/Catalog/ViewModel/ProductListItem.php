<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\ViewModel;

use Hyva\Theme\ViewModel\BlockCache;
use Hyva\Theme\ViewModel\CurrentCategory;
use Hyva\Theme\ViewModel\ProductListItem as BaseProductListItem;
use Hyva\Theme\ViewModel\ProductPage;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;

/**
 * Override to add additional_data field for block
 */
class ProductListItem extends BaseProductListItem
{
    /**
     *
     * @param LayoutInterface $layout
     * @param ProductPage $productViewModel
     * @param CurrentCategory $currentCategory
     * @param BlockCache $blockCache
     * @param CustomerSession $customerSession
     */
    // phpcs:disable
    public function __construct(
        private readonly LayoutInterface $layout,
        ProductPage $productViewModel,
        CurrentCategory $currentCategory,
        private readonly BlockCache $blockCache,
        CustomerSession $customerSession
    ) {
        parent::__construct($layout, $productViewModel, $currentCategory, $blockCache, $customerSession);
    }
    // phpcs:enable

    /**
     * @param AbstractBlock $itemRendererBlock
     * @param Product $product
     * @param AbstractBlock $parentBlock
     * @param string $viewMode
     * @param string $templateType
     * @param string $imageDisplayArea
     * @param bool $showDescription
     * @param array $additionalData
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItemHtmlWithRenderer(
        AbstractBlock $itemRendererBlock,
        Product $product,
        AbstractBlock $parentBlock,
        string $viewMode,
        string $templateType,
        string $imageDisplayArea,
        bool $showDescription,
        array $additionalData = []
    ): string {
        // Careful! Temporal coupling!
        // First the values on the block need to be set, then the cache key info array can be created.

        $itemRendererBlock->setData('product', $product)
            ->setData('view_mode', $viewMode)
            ->setData('image_display_area', $imageDisplayArea)
            ->setData('show_description', $showDescription)
            ->setData('position', $parentBlock->getPositioned())
            ->setData('pos', $parentBlock->getPositioned())
            ->setData('additional_data', $additionalData)
            ->setData('template_type', $templateType)
            ->setData('cache_lifetime', 3600)
            ->setData('cache_tags', $product->getIdentities());

        $itemCacheKeyInfo = $this->getItemCacheKeyInfo($product, $parentBlock, $viewMode, $templateType);
        $itemRendererBlock->setData('cache_key', $this->blockCache->hashCacheKeyInfo($itemCacheKeyInfo));

        foreach (($itemRendererBlock->getData('additional_item_renderer_processors') ?? []) as $processor) {
            if (method_exists($processor, 'beforeListItemToHtml')) {
                //phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                call_user_func([$processor, 'beforeListItemToHtml'], $itemRendererBlock, $product);
            }
        }

        return $itemRendererBlock->toHtml();
    }

    /**
     * @param Product $product
     * @param AbstractBlock $parentBlock
     * @param string $viewMode
     * @param string $templateType
     * @param string $imageDisplayArea
     * @param bool $showDescription
     * @param array $additionalData
     * @param int $counter
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItemHtml(
        Product $product,
        AbstractBlock $parentBlock,
        string $viewMode,
        string $templateType,
        string $imageDisplayArea,
        bool $showDescription,
        array $additionalData = [],
        int $counter = 0
    ): string {
        /** @var AbstractBlock $itemRendererBlock */
        $itemRendererBlock = $this->layout->getBlock('product_list_item');

        if ($counter < 3) {
            $additionalData['preload_image'] = true;
        }

        return $this->getItemHtmlWithRenderer(
            $itemRendererBlock,
            $product,
            $parentBlock,
            $viewMode,
            $templateType,
            $imageDisplayArea,
            $showDescription,
            $additionalData
        );
    }
}
