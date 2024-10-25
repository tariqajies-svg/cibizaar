<?php
/**
 * This file is part of the Magebit_StockFilter package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_StockFilter
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\StockFilter\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\CatalogInventory\Model\Stock as CatalogInventoryStock;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stock extends AbstractFilter
{
    private const CONFIG_FILTER_LABEL_PATH = 'stockfilter/settings/label';
    private const CONFIG_URL_PARAM_PATH = 'stockfilter/settings/url_param';
    protected $_requestVar = 'in-stock';

    /**
     * @param ItemFactory $filterItemFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param AttributeFactory $attributeFactory
     * @param StripTags $tagFilter
     * @param RequestInterface $request
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        private readonly AttributeFactory $attributeFactory,
        private readonly StripTags $tagFilter,
        private readonly RequestInterface $request,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->configureAttributeModel();
        $this->_requestVar = $this->scopeConfig->getValue(
            self::CONFIG_URL_PARAM_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function apply(RequestInterface $request): self
    {
        $filter = $request->getParam($this->getRequestVar());
        if ($filter === null) {
            return $this;
        }

        $filter = (int) $filter;

        $layerState = $this->getLayer()->getState();

        $filterItem = $this->_createItem(
            $this->tagFilter->filter($this->getLabel($filter)),
            $filter
        );
        $layerState->addFilter($filterItem);

        return $this;
    }

    /**
     * Get filter name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_FILTER_LABEL_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get data array for building status filter items
     *
     * @return array
     * @throws StateException
     */
    protected function _getItemsData(): array
    {
        /** @var Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $optionsFacetedData = $productCollection->getFacetedData('stock');
        $attributeValue = $this->request->getParam($this->_requestVar);
        $data = [];
        foreach ($this->getStatuses() as $status) {
            $count = $optionsFacetedData[$status]['count'] ?? 0;
            if (($attributeValue === null || (int) $attributeValue === (int) $status) && $count > 0) {
                $data[] = [
                    'label' => $this->tagFilter->filter($this->getLabel($status)),
                    'value' => $status,
                    'count' => $count
                ];
            }
        }

        return $data;
    }

    /**
     * Get available statuses
     * @return array
     */
    public function getStatuses(): array
    {
        return [
            CatalogInventoryStock::STOCK_IN_STOCK,
            CatalogInventoryStock::STOCK_OUT_OF_STOCK
        ];
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return [
            CatalogInventoryStock::STOCK_IN_STOCK => __('In Stock'),
            CatalogInventoryStock::STOCK_OUT_OF_STOCK => __('Out of Stock')
        ];
    }

    /**
     * @param $value
     * @return Phrase
     */
    public function getLabel($value): Phrase
    {
        $labels = $this->getLabels();
        if (isset($labels[$value])) {
            return $labels[$value];
        }
        return __('');
    }

    /**
     * Set filter items to selected
     *
     * @return $this
     */
    protected function _initItems(): self
    {
        parent::_initItems();
        $attributeValue = $this->request->getParam($this->_requestVar);
        foreach ($this->_items as $item) {
            $applyValue = $item->getValue();
            if ($attributeValue !== null && (int) $applyValue === (int) $attributeValue) {
                $item->setIsSelected(true);
                $item->setApplyFilterValue([]);
            } else {
                $item->setApplyFilterValue($applyValue);
            }
        }

        return $this;
    }

    /**
     * Add some dummy attribute data to allow this filter to show up properly
     *
     * @return void
     */
    private function configureAttributeModel(): void
    {
        $attribute = $this->attributeFactory->create();

        $attribute->setFacetMaxSize(2);
        $this->setAttributeModel($attribute);
    }
}
