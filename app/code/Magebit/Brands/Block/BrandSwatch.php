<?php
/**
 * This file is part of the Magebit_Brands package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Brands
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Brands\Block;

use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Block\Product\View;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Store\Model\StoreManagerInterface;

class BrandSwatch extends View
{
    /**
     * @param Media $mediaHelper
     * @param CollectionFactory $categoryCollection
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param ProductHelper $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @return void
     */
    public function __construct(
        private readonly Media $mediaHelper,
        private readonly CollectionFactory $categoryCollection,
        private readonly StoreManagerInterface $storeManager,
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoderInterface $jsonEncoder,
        StringUtils $string,
        ProductHelper $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return null|Product
     */
    public function getProduct(): ?Product
    {
        return $this->getData('product');
    }

    /**
     * @return null|array
     */
    public function getBrandLogo(): ?array
    {
        $product = $this->getProduct();
        if ($product->getManufacturer()) {
            $category = $this->categoryCollection->create()
                ->addAttributeToSelect('brand_logo')
                ->addAttributeToSelect('name')
                ->addFieldToFilter('brand', ['eq' => $product->getManufacturer()])
                ->getFirstItem();

            if ($category) {
                if ($image = $category->getBrandLogo()) {
                    $imageConfig = $this->mediaHelper->getImageConfig();
                    $data['image'] = $image;
                    $data['image_url'] = $this->storeManager->getStore()->getBaseUrl() . $image;
                    $data['height'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['height'];
                    $data['width'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['width'];
                    $data['brand'] = $category->getName();
                    return $data;
                }
            }
        }

        return null;
    }

    public function getBrandLogoPDP($product): ?array
    {
        if ($product->getManufacturer()) {
            $category = $this->categoryCollection->create()
                ->addAttributeToSelect('brand_logo')
                ->addAttributeToSelect('name')
                ->addFieldToFilter('brand', ['eq' => $product->getManufacturer()])
                ->getFirstItem();

            if ($category) {
                if ($image = $category->getBrandLogo()) {
                    $imageConfig = $this->mediaHelper->getImageConfig();
                    $data['image'] = $image;
                    $data['image_url'] = $this->storeManager->getStore()->getBaseUrl() . $image;
                    $data['height'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['height'];
                    $data['width'] = $imageConfig[Swatch::SWATCH_IMAGE_NAME]['width'];
                    $data['brand'] = $category->getName();
                    return $data;
                }
            }
        }

        return null;
    }
}
