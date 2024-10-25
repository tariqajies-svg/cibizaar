<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
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
namespace Magebit\HyvaRequisitionLists\Plugin\Item\Listing\Column;

use Aheadworks\RequisitionLists\Model\Product\DetailProvider\Pool;
use Aheadworks\RequisitionLists\Ui\Component\RequisitionList\Item\Listing\Column\Price as PriceOrigin;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Adjustment\Calculator as AdjustmentCalculator;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutFactory;

class Price extends PriceOrigin
{
    private ?RendererPool $rendererPool = null;

    // @codingStandardsIgnoreStart
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ProductRepositoryInterface $productRepository
     * @param LayoutFactory $layoutFactory
     * @param AdjustmentCalculator $adjustmentCalculator
     * @param Pool $pool
     * @param PriceCurrency $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        private readonly LayoutFactory $layoutFactory,
        private readonly AdjustmentCalculator $adjustmentCalculator,
        private readonly Pool $pool,
        private readonly PriceCurrency $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $productRepository,
            $layoutFactory,
            $adjustmentCalculator,
            $pool,
            $priceCurrency,
            $components,
            $data
        );
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['price'] = $this->getPriceHtml($item);
                $item['price_value'] = $this->getPrice($item);
            }
        }

        return $dataSource;
    }

    /**
     * @return bool|BlockInterface
     * @throws LocalizedException
     */
    private function getRenderPool(): bool|BlockInterface
    {
        if ($this->rendererPool === null) {
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->load('catalog_product_prices');
            $layout->generateXml();
            $layout->generateElements();
            $this->rendererPool = $layout->getBlock('render.product.prices');
        }

        return $this->rendererPool;
    }

    /**
     * Get price
     *
     * @param array $item
     * @return string
     * @throws LocalizedException
     */
    private function getPrice(array $item): string
    {
        $provider = $this->pool->getProvider($item);
        $price = $provider->getFinalPriceForBuyRequest();

        if (!$price) {
            $price = $provider->getProduct()->getPrice();
        }

        return (string) $price;
    }

    /**
     * Get price html
     *
     * @param array $item
     * @return string
     * @throws LocalizedException
     */
    private function getPriceHtml(array $item): string
    {
        $provider = $this->pool->getProvider($item);
        $rendererPool = $this->getRenderPool();
        $price = $provider->getFinalPriceForBuyRequest();
        if ($price) {
            $amount = $this->adjustmentCalculator->getAmount(
                $this->priceCurrency->convert($price),
                $provider->getProduct()
            );
            $priceRender = $rendererPool->createAmountRender($amount, $provider->getProduct());
        } else {
            $priceRender = $rendererPool->createPriceRender(
                FinalPrice::PRICE_CODE,
                $provider->getProduct(),
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $priceRender->toHtml();
    }
}
