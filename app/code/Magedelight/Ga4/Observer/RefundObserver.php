<?php
/**
 *
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Observer;

use Magedelight\Ga4\Helper\Data;
use Magento\Framework\Event\Observer;
use Magedelight\Ga4\Model\EventTrigger;

class RefundObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $adminSession;

    protected $_productloader;

    /**
     * @param Data $datahelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(
        Data $mdhelper,
        EventTrigger $eventTrigger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\Session $adminSession,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->mdhelper = $mdhelper;
        $this->eventTrigger = $eventTrigger;
        $this->orderRepository = $orderRepository;
        $this->adminSession = $adminSession;
        $this->_productloader = $_productloader;
    }

    public function execute(Observer $observer)
    {
       /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        try {
            if ($this->mdhelper->isGTMStatus() && $this->mdhelper->GTMEventConfigured('refund')) {
                $items = [];
                $creditmemo = $observer->getEvent()->getCreditmemo();
                $order = $this->orderRepository->get($creditmemo->getOrderId());
                $items = $this->getPurchaseItems($creditmemo);
                $data = [
                    'event' => 'refund',
                    'ecommerce' => [
                        'currency' => $order->getOrderCurrencyCode(),
                        'transaction_id' =>  $order->getIncrementId(),
                        'value' => $this->getPurchaseAmount($order),
                        'coupon' => ($order->getCouponCode()) ? (string)$order->getCouponCode():null,
                        'shipping' => number_format($creditmemo->getShippingAmount(), 2, '.', ''),
                        'tax' => number_format($creditmemo->getTaxAmount(), 2, '.', ''),
                        'items' => [
                            $items
                        ]
                    ]
                ];
            
                if ($data) {
                    $this->adminSession->setRefundData($data);
                    $dataevent =[
                        'event' => 'refund',
                        'ecommerce' => $data
                    ];
                    $this->mdhelper->setTagManagerReportData($dataevent);
                }
            }
        } catch (Exception $e) {
            //throw new Exception($e->getMessage(), 1);
        }
    }

    private function getPurchaseItems($creditmemo)
    {
        $products = [];
        $type = $this->mdhelper->getProductTypeForCheckout();
        $k = 0;
        
        foreach ($creditmemo->getAllItems() as $value) {
            if ($value->getOrderItem()->getParentItem()) {
                continue;
            }
            $originProduct = $this->getLoadProduct($value->getProductId());
            $productCollection = $originProduct;
            if ($type == \Magedelight\Ga4\Model\Config\Source\ProductType::CHILD) {
                if ($value->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    $getitems = $value->getChildrenItems();
                    foreach ($getitems as $secondLevelItem) {
                        $productCollection = $secondLevelItem->getProduct();
                    }
                }
            }
            
            $itemsInfo = [];
            $itemsInfo['item_id'] = $this->mdhelper->getProductIdentifier($productCollection);
            $itemsInfo['item_name'] = $value->getName();
            $itemsInfo['affiliation'] = $this->mdhelper->getNameOfAffilation();
            $itemsInfo['index'] = $k;
            if ($this->mdhelper->isBrandEnabled()) {
                $itemsInfo['item_brand'] = $this->mdhelper->getBrandData($originProduct);
            }
            $categoryName = $this->mdhelper->getCatIds($productCollection->getCategoryIds());
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
            }
        
            $catIdsOfProducts = $productCollection->getCategoryIds();
            if (!empty($categoryName)) {
                $itemsInfo['item_list_name'] = implode('/', $categoryName);
            }
            $itemsInfo['item_list_id'] = count($catIdsOfProducts) ? $catIdsOfProducts[0] : '';
            if ($this->mdhelper->getVariantStatus()) {
                $option = $value->getData('product_options');
                $ptype = $value->getData('product_type');
                $variant = $this->mdhelper->getProductOption($option, $ptype);
                if ($variant) {
                    $productDetail['item_variant'] = $variant;
                }
            }
            $itemsInfo['price'] = number_format($value->getPrice(), 2, '.', '');
            $itemsInfo['quantity'] = $value->getQty();

            $customAttribute = $this->mdhelper->getProductCustomAttribute($originProduct);
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

    private function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

   /* private function getPriceBasedUponConfig($value)
    {
        $currencyOption = $this->mdhelper->getCurrencyOption();
        $finalPrice = 0;
        $price = $value->getPrice();
        if ($price > 0) {
            if ($currencyOption == "base_currency" && $currencyOption != "store_currency") {
                $price = $value->getPrice();
                if ($this->mdhelper->getSummaryExcludeTax() == 1) {
                    $price -= $value->getBaseTaxAmount();
                } else {
                    $price = $value->getBasePriceInclTax();
                }
            }

            if ($currencyOption != "base_currency" && $currencyOption == "store_currency") {
                $price = $value->getPrice();
                if ($this->mdhelper->getSummaryExcludeTax() == 1) {
                    $price -= $value->getTaxAmount();
                } else {
                    $price = $value->getPriceInclTax();
                }
            }
        }

        if ($price) {
            $finalPrice = number_format($price, 2, '.', '');
        }
        return $finalPrice;
    }*/

    private function getPurchaseAmount($order)
    {
        $isAllowOrderTotalCalculation = $this->mdhelper->getIsAllowOrderSummary();
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

    public function getOrderGrandTotal($order)
    {
        $orderTotal = $order->getGrandtotal();

        if ($this->mdhelper->getSummaryExcludeTax()) {
            $orderTotal -= $order->getTaxAmount();
        }

        if ($this->mdhelper->getSummaryExcludeShipping()) {
            $orderTotal -= $order->getShippingAmount();
        }

        return $orderTotal;
    }

    public function getOrderSubTotal($order)
    {
        $orderTotal = $order->getSubtotal();
        return $orderTotal;
    }
}
