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

namespace Magebit\HyvaRequisitionLists\ViewModel\ProductList;

use Aheadworks\RequisitionLists\Api\Data\RequisitionListInterface;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Item\Collection;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Item\CollectionFactory as ListProductCollection;
use Magebit\HyvaRequisitionLists\ViewModel\RequisitionList;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageFactory as ProductImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class RequisitionListProducts extends Template
{
    private $productCollection;

    /**
     * @var ListProductCollection
     */
    private ListProductCollection $listProductCollection;
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;
    /**
     * @var RequisitionList
     */
    private RequisitionList $requisitionList;
    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;
    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;
    /**
     * @var EavConfig
     */
    private EavConfig $eavConfig;
    /**
     * @var ProductImageFactory
     */
    private ProductImageFactory $productImageFactory;

    /**
     * @param ListProductCollection $listProductCollection
     * @param CustomerSession $customerSession
     * @param Context $context
     * @param RequisitionList $requisitionList
     * @param ProductCollectionFactory $productCollectionFactory
     * @param PriceHelper $priceHelper
     * @param EavConfig $eavConfig
     * @param ProductImageFactory $productImageFactory
     * @param array $data
     */
    public function __construct(
        ListProductCollection $listProductCollection,
        CustomerSession $customerSession,
        Template\Context $context,
        RequisitionList $requisitionList,
        ProductCollectionFactory $productCollectionFactory,
        PriceHelper $priceHelper,
        EavConfig $eavConfig,
        ProductImageFactory $productImageFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->listProductCollection = $listProductCollection;
        $this->customerSession = $customerSession;
        $this->requisitionList = $requisitionList;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->priceHelper = $priceHelper;
        $this->eavConfig = $eavConfig;
        $this->productImageFactory = $productImageFactory;
    }

    /**
     * @param string $listId
     * @return Collection|array
     */
    public function getListProductCollection(string $listId)
    {
        if ($this->customerSession->getCustomerId()) {
            $listProductCollection = $this->listProductCollection->create();
            $productCollectionByListId = $listProductCollection
                ->addFieldToFilter('list_id', $listId);

            $this->productCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('ean_code')
                ->addFieldToFilter('sku', ['in' => $productCollectionByListId->getColumnValues('product_sku')])
                ->addFinalPrice()
                ->addAttributeToSelect('thumbnail');

            return $productCollectionByListId;
        }
        return [];
    }

    /**
     * @param $productOptions
     * @return array
     * @throws LocalizedException
     */
    public function getConfigurableOptions($productOptions): array
    {
        $attributeOptions = [];
        foreach ($productOptions->getExtensionAttributes()->getConfigurableItemOptions() as $productOption) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $productOption->getOptionId());
            foreach ($attribute->getOptions() as $option) {
                if ($productOption->getOptionValue() == $option->getValue()) {
                    $attributeOptions[] = [
                        'attribute_name' => $attribute->getStoreLabel(),
                        'attribute_value' => $option->getLabel()
                    ];
                }
            }
        }

        return $attributeOptions;
    }

    /**
     * @param string $sku
     * @return float|string
     */
    public function getProductPrice(string $sku)
    {
        return $this->priceHelper->currency($this->getProductCollection()->getItemByColumnValue('sku', $sku)->getPrice(), true, false);
    }

    /**
     * @param string $sku
     * @return string|null
     */
    public function getProductUrl(string $sku): ?string
    {
        return $this->getProductCollection()->getItemByColumnValue('sku', $sku)->getProductUrl();
    }

    /**
     * @param string $sku
     * @return Image
     */
    public function getProductImage(string $sku): Image
    {
        /** @var Product $product */
        $product = $this->getProductCollection()
            ->getItemByColumnValue('sku', $sku);

        return $this->productImageFactory->create($product, 'aw_rl_rlist_edit', []);
    }

    /**
     * @param string $sku
     * @return string|null
     */
    public function getProductEanCode(string $sku): ?string
    {
        return $this->getProductCollection()->getItemByColumnValue('sku', $sku)->getEanCode();
    }

    /**
     * @return ProductCollection
     */
    public function getProductCollection(): ProductCollection
    {
        return $this->productCollection;
    }

    /**
     * @return RequisitionListInterface[]|array
     */
    public function getRequisitionLists(): array
    {
        return $this->requisitionList->getSearchResults();
    }
}
