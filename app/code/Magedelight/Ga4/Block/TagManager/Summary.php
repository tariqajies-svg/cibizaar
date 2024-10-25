<?php
/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magedelight\Ga4\Model\Config\Source\ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Ga4\Model\DataSaving;
use Magedelight\Ga4\Helper\Data;
use Magedelight\Ga4\Model\CookieData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Summary extends \Magedelight\Ga4\Block\TagManager\Manager
{
    /**
     * @var \Magedelight\Ga4\Model\DataSaving
     */
    protected $datasaving;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magedelight\Ga4\Model\CookieData
     */
    protected $storedData;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

     /**
      * Construct
      *
      * @param Context $context
      * @param DataSaving $datasaving
      * @param Data $helper
      * @param CookieData $storedData
      * @param CollectionFactory $orderCollectionFactory
      * @param \Magento\Catalog\Model\ProductFactory $_productloader
      * @param array $data
      */
    public function __construct(
        Context $context,
        DataSaving $datasaving,
        Data $helper,
        CookieData $storedData,
        CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_productloader = $_productloader;
        parent::__construct($context, $datasaving, $helper, $storedData, $orderCollectionFactory, $data);
    }

    /**
     * GetPurchaseItems
     */
    public function getPurchaseItems()
    {
        $quote = $this->getOrder();
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();
        $code = $this->helper->getCurrencyCode();
        $k = 0;
        foreach ($quote->getAllVisibleItems() as $value) {
            $product = $value->getProduct();
            $productCollection = $product;
            if ($type == ProductType::CHILD) {
                if ($value->getProductType()== Configurable::TYPE_CODE) {
                    $getitems = $value->getChildrenItems();
                    foreach ($getitems as $secondLevelItem) {
                        $productCollection = $secondLevelItem->getProduct();
                    }
                }
            }

            $itemsInfo = [];
            /*$itemsInfo['currency'] = $code;*/
            $itemsInfo['item_id'] = $this->helper->getProductIdentifier($productCollection);
            $itemsInfo['item_name'] = $value->getName();
            $itemsInfo['affiliation'] = $this->helper->getNameOfAffilation();
            $itemsInfo['coupon'] = $quote->getCouponCode();
            $itemsInfo['index'] = $k;
            $originProduct = $this->getLoadProduct($value->getProductId());
            if ($this->helper->isBrandEnabled()) {
                $itemsInfo['item_brand'] = $this->helper->getBrandData($originProduct);
            }
            $categoryName = $this->helper->getCatIds($productCollection->getCategoryIds());
            if (!empty($categoryName)) {
                $j=1;
                for ($i=0; $i < count($categoryName); $i++) {
                    if ($j>1) {
                        $itemsInfo['item_category'.$i] = $categoryName[$i];
                    } else {
                        $itemsInfo['item_category'] = $categoryName[$i];
                    }
                    $j++;
                }
                $itemsInfo['item_list_name'] = implode('/', $categoryName);
                $catIdsOfProducts = $productCollection->getCategoryIds();
                $itemsInfo['item_list_id'] = count($catIdsOfProducts) ? $catIdsOfProducts[0] : '';
            }
            
            
            

            if ($this->helper->getVariantStatus()) {
                $option = $value->getData('product_options');
                $ptype = $value->getData('product_type');
                $variant = $this->helper->getProductOption($option, $ptype);
                if ($variant) {
                    $itemsInfo['item_variant'] = $variant;
                }
            }

            $itemsInfo['price'] = $this->getItemPrice($value);
            $itemsInfo['quantity'] = (int)$value->getQtyOrdered();

            $customAttribute = $this->helper->getProductCustomAttribute($originProduct);
            if (!empty($customAttribute)) {
                foreach ($customAttribute as $key => $value) {
                    $itemsInfo[$key] = $value;
                }
            }
            
            $products[] = $itemsInfo;
            $k++;
        }

        return $products;
    }

    /**
     * GetPurchaseAmount
     */
    public function getPurchaseAmount()
    {
        $isAllowOrderTotalCalculation = $this->helper->getIsAllowOrderSummary();
        $order =  $this->getOrder();
        switch ($isAllowOrderTotalCalculation) {
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_SUBTOTAL:
                $orderGrandTotal = $this->getOrderSubTotal($order);
                break;
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_TOTAL:
            default:
                $orderGrandTotal = $this->getOrderGrandTotal($order);
                break;
        }
        $orderGrandTotal = number_format($orderGrandTotal, 2, '.', '');
        return $orderGrandTotal;
    }

    /**
     * GetPurchaseItemsIds
     */
    public function getPurchaseItemsIds()
    {
        $order = $this->getOrder();
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();

        foreach ($order->getAllVisibleItems() as $value) {
            $product = $value->getProduct();
            if ($type == ProductType::CHILD) {
                if ($value->getProductType()== Configurable::TYPE_CODE) {
                    $getitems = $value->getChildrenItems();
                    foreach ($getitems as $secondLevelItem) {
                        $productCollection = $secondLevelItem->getProduct();
                    }
                }
            }
            $products[] = $this->helper->getProductIdentifier($productCollection);
        }

        return $products;
    }

    /**
     * GetOrderTotalCount
     */
    public function getOrderTotalCount()
    {
        $order =  $this->getOrder();
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return 1;
        }

        $collection = $this->orderCollectionFactory->create($customerId);
        return $collection->count();
    }

    /**
     * GetAllTotalCount
     */
    public function getAllTotalCount()
    {
        $order =  $this->getOrder();
        $customerId = $order->getCustomerId();
        $currencyOption = $this->helper->getCurrencyOption();
        if (!$customerId) {
            if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
                $orderValue = $order->getBaseGrandtotal();
            }
            if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
                $orderValue = $order->getGrandtotal();
            }
            return $orderValue;
        }

        $orderTotals = $this->orderCollectionFactory->create($customerId)
            ->addFieldToSelect('*');

        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $grandTotals = $orderTotals->getColumnValues('base_grand_total');
            $refundTotals = $orderTotals->getColumnValues('base_total_refunded');
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $grandTotals = $orderTotals->getColumnValues('grand_total');
            $refundTotals = $orderTotals->getColumnValues('total_refunded');
        }
        return array_sum($grandTotals) - array_sum($refundTotals);
    }

    /**
     * GetOrderSubTotal
     *
     * @param Order $order
     */
    public function getOrderSubTotal($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $orderTotal = $order->getBaseSubtotal();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $order->getSubtotal();
        }
        return $orderTotal;
    }

    /**
     * GetOrderGrandTotal
     *
     * @param Order $order
     */
    public function getOrderGrandTotal($order)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $orderTotal = $order->getBaseGrandtotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getBaseTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getBaseShippingAmount();
            }
        }

        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $order->getGrandtotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getShippingAmount();
            }
        }
        return $orderTotal;
    }

    /**
     * GetPriceBasedUponConfig
     *
     * @param Value $value
     */
    /*public function getPriceBasedUponConfig($value)
    {
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            $price = $value->getPrice();
            if ($this->helper->getSummaryExcludeTax() == 1) {
                $price -= $value->getBaseTaxAmount();
            } else {
                $price = $value->getBasePriceInclTax();
            }
        }

        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $price = $value->getPrice();
            if ($this->helper->getSummaryExcludeTax() == 1) {
                $price -= $value->getTaxAmount();
            } else {
                $price = $value->getPriceInclTax();
            }
        }
        $finalPrice = number_format($price, 2, '.', '');
        return $finalPrice;
    }*/

    public function getItemPrice($item){
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return number_format($item->getBasePrice(), 2, '.', '');
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return number_format($item->getPrice(), 2, '.', '');
        }
    }

    /**
     * GetLoadProduct
     *
     * @param ID $id
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * GetDataHelper
     */
    public function getDataHelper()
    {
        return $this->helper;
    }

    public function getOrderTax($order){
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return $order->getBaseTaxAmount();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $order->getTaxAmount();
        }
    }

    public function getOrderShipping($order){
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return $order->getBaseShippingAmount();
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $order->getShippingAmount();
        }
    }
}
