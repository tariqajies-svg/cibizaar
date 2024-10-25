<?php
/**
 * Magedelight
 *
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magedelight\Ga4\Block\TagManager\Manager;
use Magento\Framework\View\Element\Template\Context;
use Magedelight\Ga4\Model\DataSaving;
use Magedelight\Ga4\Helper\Data;
use Magedelight\Ga4\Model\CookieData;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ShoppingCart extends Manager
{

    public function __construct(
        Context $context,
        DataSaving $datasaving,
        Data $helper,
        CookieData $storedData,
        CollectionFactory $orderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_productloader = $_productloader;
        parent::__construct($context, $datasaving, $helper, $storedData, $orderCollectionFactory, $data);
    }

    /**
     * GetViewCartItems
     */
    public function getViewCartItems()
    {
        $quote = $this->getQuote();
        $products = [];
        $type = $this->helper->getProductTypeForCheckout();
        $k = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if ($type == \Magedelight\Ga4\Model\Config\Source\ProductType::CHILD) {
                if ($item->getProductType()==\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $children = $item->getChildren();
                    foreach ($children as $child) {
                        $product = $child->getProduct();
                    }
                }
            }
            
            $productDetail = [];
            $productDetail['item_id'] = $this->helper->getProductIdentifier($product);
            $productDetail['item_name'] = $item->getName();
            $productDetail['affiliation'] = $this->helper->getNameOfAffilation();
            $productDetail['coupon'] = $quote->getCouponCode();
            $productDetail['index'] = $k;
            $categoryName =  $this->helper->getCatIds($product->getCategoryIds());
            if (!empty($categoryName)) {
                $j=1;
                for ($i=0; $i < count($categoryName); $i++) {
                    if ($j>1) {
                        $productDetail['item_category'.$i] = $categoryName[$i];
                    } else {
                        $productDetail['item_category'] = $categoryName[$i];
                    }
                    $j++;
                }
                $productDetail['item_list_name'] = implode('/', $categoryName);
                $productCategoryIds = $product->getCategoryIds();
                $productDetail['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
            }

            if ($this->helper->getVariantStatus()) {
                $option = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $ptype = $item->getProductType();
                $variant = $this->helper->getProductOption($option, $ptype);
                if ($variant) {
                    $productDetail['item_variant'] = $variant;
                }
            }

            $productDetail['price'] = $this->getItemPrice($item);
            $productDetail['quantity'] = $item->getQty();
            
            $originProduct = $this->getLoadProduct($item->getProductId());
            if ($this->helper->isBrandEnabled()) {
                $productDetail['item_brand'] = $this->helper->getBrandData($originProduct);
            }
            $customAttribute = $this->helper->getProductCustomAttribute($originProduct);
            if (!empty($customAttribute)) {
                foreach ($customAttribute as $key => $value) {
                    $productDetail[$key] = $value;
                }
            }

            $products[] = $productDetail;
            $k++;
        }

        return $products;
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
     * GetPurchaseAmount
     */
    public function getPurchaseAmount()
    {
        $isAllowOrderTotalCalculation = $this->helper->getIsAllowOrderSummary();
        $quote = $this->getQuote();
        switch ($isAllowOrderTotalCalculation) {
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_SUBTOTAL:
                $orderGrandTotal = $this->getOrderSubTotal($quote);
                break;
            case \Magedelight\Ga4\Model\Config\Source\Summary::CHECKOUT_TOTAL:
            default:
                $orderGrandTotal = $this->getOrderGrandTotal($quote);
                break;
        }
        $orderGrandTotal = number_format($orderGrandTotal, 2, '.', '');
        return $orderGrandTotal;
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
            $orderTotal = $order->getBaseGrandTotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getShippingAddress()->getBaseTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getShippingAddress()->getBaseShippingAmount();
            }
        }

        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            $orderTotal = $order->getGrandTotal();
            if ($this->helper->getSummaryExcludeTax()) {
                $orderTotal -= $order->getShippingAddress()->getTaxAmount();
            }

            if ($this->helper->getSummaryExcludeShipping()) {
                $orderTotal -= $order->getShippingAddress()->getShippingAmount();
            }
        }
        return $orderTotal;
    }

    public function getItemPrice($item){
        $currencyOption = $this->helper->getCurrencyOption();
        if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
            return number_format($item->getPrice(), 2, '.', '');
        }
        if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
            return $this->helper->getFormatedPrice($item->getPrice());
        }
    }
}
