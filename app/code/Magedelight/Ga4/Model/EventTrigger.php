<?php
/**
 *
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

namespace Magedelight\Ga4\Model;

use Magedelight\Ga4\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as Catalog;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepo;
use Magedelight\Ga4\Model\GtagFactory;
use Magento\Sales\Api\OrderRepositoryInterface as Salesorder;

class EventTrigger
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    public $helperData;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutsession;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $stateInterface;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheInterface;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $catalogCollection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepo;

    /**
     * @var $getStoreLevelCategories
     */
    protected $getStoreLevelCategories;

    /**
     * @var \Magedelight\Ga4\Model\GtagFactory
     */
    protected $gtagFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var BlockFactory
     */
    protected $createBlock;

    /**
     * @var StoreManagerInterface
     */
    protected $store;
    protected $quoteRepository;
    protected $salesorder;
    
    /**
     * Construct
     *
     * @param Data $helperData
     * @param CheckoutSession $checkoutsession
     * @param StateInterface $stateInterface
     * @param CacheInterface $cacheInterface
     * @param Catalog $catalogCollection
     * @param BlockFactory $createBlock
     * @param StoreManagerInterface $storeInterface
     * @param OrderRepo $orderRepo
     * @param GtagFactory $gtagFactory
     * @param Http $request
     */
    public function __construct(
        Data $helperData,
        CheckoutSession $checkoutsession,
        StateInterface $stateInterface,
        CacheInterface $cacheInterface,
        Catalog $catalogCollection,
        BlockFactory $createBlock,
        StoreManagerInterface $storeInterface,
        OrderRepo $orderRepo,
        GtagFactory $gtagFactory,
        \Magento\Framework\App\Request\Http $request,
        Salesorder $salesorder
    ) {
        $this->helperData = $helperData;
        $this->checkoutsession = $checkoutsession;
        $this->stateInterface = $stateInterface;
        $this->cacheInterface = $cacheInterface;
        $this->catalogCollection = $catalogCollection;
        $this->createBlock = $createBlock;
        $this->store = $storeInterface;
        $this->orderRepo = $orderRepo;
        $this->gtagFactory = $gtagFactory;
        $this->request = $request;
        $this->getStoreLevelCategories = [];
        $this->salesorder = $salesorder;
    }

    /**
     * CartEventTrigger
     *
     * @param Event $event
     * @param Qty $qty
     * @param Product $product
     * @param Price $price
     */
    public function cartEventTrigger($event, $qty, $product, $price, $variant = null)
    {
        $push = [];
        $productPushData = [];
        $push['event'] = $event;
        $push['ecommerce'] = [];
        $push['ecommerce']['currency'] = $this->helperData->getCurrencyCode();
        $push['ecommerce']['value'] = $price;
        $push['ecommerce']['items'] = [];
        $productPushData['item_id'] = $this->helperData->getProductIdentifier($product);
        $productPushData['item_name'] = $product->getData('name');
        $productPushData['affiliation'] = $this->helperData->getNameOfAffilation();
        if ($this->helperData->isBrandEnabled()) {
            $productPushData['item_brand'] = $this->helperData->getBrandData($product);
        }
        $categoryName = $this->helperData->getCatIds($product->getCategoryIds());
        if (!empty($categoryName)) {
                $j=1;
            for ($i=0; $i < count($categoryName); $i++) {
                if ($j>1) {
                    $productPushData['item_category'.$i] = $categoryName[$i];
                } else {
                    $productPushData['item_category'] = $categoryName[$i];
                }
                $j++;
            }
                $productPushData['item_list_name'] = implode('/', $categoryName);
                $productCategoryIds = $product->getCategoryIds();
                $productPushData['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
        }
        if ($variant) {
            $productPushData['item_variant'] = $variant;
        }
        $productPushData['price'] = $price;
        $productPushData['quantity'] = $qty;

        $customAttribute = $this->helperData->getProductCustomAttribute($product);
        if (!empty($customAttribute)) {
            foreach ($customAttribute as $key => $value) {
                $productPushData[$key] = $value;
            }
        }

        $push['ecommerce']['items'][] = $productPushData;

        $dataevent =[
            'event' => $push['event'],
            'ecommerce' => $push
        ];
        
        $this->helperData->setTagManagerReportData($dataevent);

        return $push;
    }

    /**
     * ItemAddedEventTrigger
     *
     * @param Event $event
     * @param Product $product
     */
    public function itemAddedEventTrigger($event, $product)
    {
        $push = [];
        $itemPushData = [];
        $push['event'] = $event;
                
       /* $push['ecommerce']['action'] = [];
        $push['ecommerce']['action']['items'] = [];*/
        $push['ecommerce']['items'] = [];
        $itemPushData['item_id'] = $this->helperData->getProductIdentifier($product);
        $itemPushData['item_name'] = $product->getName();
        $itemPushData['affiliation'] = $this->helperData->getNameOfAffilation();
        if ($this->helperData->isBrandEnabled()) {
            $itemPushData['item_brand'] = $this->helperData->getBrandData($product);
        }
        $categoryName = $this->helperData->getCatIds($product->getCategoryIds());
        if (!empty($categoryName)) {
            $j=1;
            for ($i=0; $i < count($categoryName); $i++) {
                if ($j>1) {
                    $itemPushData['item_category'.$i] = $categoryName[$i];
                } else {
                    $itemPushData['item_category'] = $categoryName[$i];
                }
                $j++;
            }
            $itemPushData['item_list_name'] = implode('/', $categoryName);
            $productCategoryIds = $product->getCategoryIds();
            $itemPushData['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
        }
        $itemPushData['price'] = $this->helperData->getPriceFormat($product);

        $customAttribute = $this->helperData->getProductCustomAttribute($product);
        if (!empty($customAttribute)) {
            foreach ($customAttribute as $key => $value) {
                $itemPushData[$key] = $value;
            }
        }

        $push['ecommerce'] = [];
        $push['ecommerce']['currency'] = $this->helperData->getStoreCurrencyCode();
        $push['ecommerce']['value'] = $itemPushData['price'];
        $push['ecommerce']['items'][] = $itemPushData;
        
        $dataevent =[
            'event' => $push['event'],
            'ecommerce' => $push
        ];

        $this->helperData->setTagManagerReportData($dataevent);

        return $push;
    }

    /**
     * ItemAddedEventTrigger
     *
     * @param Step $step
     * @param CheckoutOption $checkoutOption
     * @param EventName $eventName
     */
    public function paymentCheckoutEventTrigger($step, $checkoutOption, $eventName)
    {
        $dataFinal = [];
        $dataFinal['event'] = $eventName;
        $dataFinal['ecommerce']['currency'] = $this->helperData->getCurrencyCode();
       
        //$dataFinal['eventLabel'] = 'Checkout';
        
       // $dataFinal['ecommerce']['item']['currencyCode'] = $this->helperData->getCurrencyCode();
        /*$dataFinal['ecommerce']['item']['checkout']['contentScope'] =  [
            'step' => $step,
            'option' => $checkoutOption
        ];*/

        $products = [];
        $checkoutBlock = $this->newSection('Payment', 'payment.phtml');

        if ($checkoutBlock) {
            $orderId = $this->checkoutsession->getLastRealOrder()->getId();
            $salesData = $this->salesorder->get($orderId);
            $checkoutBlock->setQuote($salesData);
            $products = $checkoutBlock->getCheckoutItems();
            $dataFinal['ecommerce']['value'] = number_format($checkoutBlock->getPurchaseAmount(), 2, '.', '');
        }
        $dataFinal['ecommerce']['coupon'] = ($salesData->getCouponCode()) ? (string)$salesData->getCouponCode():null;
        $dataFinal['ecommerce']['payment_type'] = $checkoutOption;
        $dataFinal['ecommerce']['items'] = [];
        $dataFinal['ecommerce']['items'] = $products;

       /* $dataFinalwithOpt['event'] = 'checkoutOption';
        $dataFinalwithOpt['eventLabel'] = 'Checkout Steps';
        $dataFinalwithOpt['item'] = [];
        $dataFinalwithOpt['item']['currencyCode'] = $this->helperData->getCurrencyCode();
        $dataFinalwithOpt['item']['checkout_option'] = [];
        $optionData = [];
        $optionData['step'] = $step;
        $optionData['option'] = $checkoutOption;
        $dataFinalwithOpt['item']['checkout_option']['contentScope'] = $optionData;*/

        $data = [];
        $data[] = $dataFinal;
        //$data[] = $dataFinalwithOpt;

        $dataevent =[
            'event' => 'add_payment_info',
            'ecommerce' => $data
        ];
        $this->helperData->setTagManagerReportData($dataevent);

        return $data;
    }

    /**
     * ShipingCheckoutEventTrigger
     *
     * @param Step $step
     * @param CheckoutOption $checkoutOption
     * @param EventName $eventName
     */
    public function shipingCheckoutEventTrigger($step, $checkoutOption, $getShippingInfoPrice, $eventName)
    {

        $dataFinal = [];
        $dataFinal['event'] = $eventName;
        $dataFinal['ecommerce']['currency'] = $this->helperData->getCurrencyCode();
        
       // $dataFinal['eventLabel'] = 'Checkout';
        
        /*$dataFinal['ecommerce']['item']['currencyCode'] = $this->helperData->getCurrencyCode();*/
        /*$dataFinal['ecommerce']['item']['checkout']['contentScope'] =  [
            'step' => $step,
            'option' => $checkoutOption
        ];*/

        $products = [];
        $checkoutBlock = $this->newSection('Shipping', 'shipping.phtml');

        if ($checkoutBlock) {
            $quote = $this->checkoutsession->getQuote();
            $checkoutBlock->setQuote($quote);
            $products = $checkoutBlock->getCheckoutItems();
            $dataFinal['ecommerce']['value'] = $checkoutBlock->getPurchaseAmount();
        }
        $dataFinal['ecommerce']['coupon'] = $quote->getCouponCode();
        $dataFinal['ecommerce']['shipping_tier'] = $checkoutOption;
        $dataFinal['ecommerce']['items'] = [];
        $dataFinal['ecommerce']['items'] = $products;

        /*$dataFinalwithOpt['event'] = 'checkoutOption';
        $dataFinalwithOpt['eventLabel'] = 'Checkout Steps';
        $dataFinalwithOpt['item'] = [];
        $dataFinalwithOpt['item']['currencyCode'] = $this->helperData->getCurrencyCode();
        $dataFinalwithOpt['item']['checkout_option'] = [];*/
        /*$optionData = [];
        $optionData['step'] = $step;
        $optionData['option'] = $checkoutOption;
        $dataFinalwithOpt['item']['checkout_option']['contentScope'] = $optionData;*/

        $data = [];
        $data[] = $dataFinal;
        //$data[] = $dataFinalwithOpt;

        $dataevent =[
            'event' => 'add_shipping_info',
            'ecommerce' => $data
        ];

        $this->helperData->setTagManagerReportData($dataevent);
        return $data;
    }

    /**
     * NewSection
     *
     * @param $blockName
     * @param $template
     */
    public function newSection($blockName, $template)
    {
        if ($block = $this->createBlock->createBlock('\Magedelight\Ga4\Block\TagManager\\' . $blockName)
            ->setTemplate('Magedelight_Ga4::' . $template)
        ) {
            return $block;
        }

        return false;
    }

    /**
     * GtagCode
     */
    public function gtagCode()
    {
        $jsCode = '';
        if (!($block = $this->newSection('Manager', 'gtag.phtml'))) {
            return $jsCode;
        }
        $block->setNameInLayout('md.gtm.datalayer.jscode');
        $this->getLandingPagedata();
        $this->getItemsData();
        $this->getViewCartItemData();
        $this->getCheckoutData();
        $this->getOrderData();
        $html = $block->toHtml();
        return $html;
    }

    /**
     * GetLandingPagedata
     */
    public function getLandingPagedata()
    {
        $getCurrentCatData = $this->helperData->getCurrentCategory();

        if (!empty($getCurrentCatData)) {
            $landingPage = $this->newSection('CatLandingPage', 'catlandingpage.phtml');

            if ($landingPage) {
                $landingPage->setCurrentCategory($getCurrentCatData);
                $landingPage->toHtml();
            }
        }
    }

    /**
     * GetItemsData
     */
    public function getItemsData()
    {
        $currentProduct = $this->helperData->getCurrentProduct();

        if (!empty($currentProduct)) {
            $productBlock = $this->newSection('ViewItem', 'viewitem.phtml');

            if ($productBlock) {
                $productBlock->setCurrentProduct($currentProduct);
                $productBlock->toHtml();
            }
        }
    }

    /**
     * GetViewCartItemData
     */
    public function getViewCartItemData()
    {
        $actionUrl = $this->request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->request->getActionName();

        if ($actionUrl == 'checkout/cart/index') {
            $data = $this->newSection('ShoppingCart', 'shoppingcart.phtml');

            if ($data) {
                $quote = $this->checkoutsession->getQuote();
                $data->setQuote($quote);
                $data->toHtml();
            }
        }
    }

    /**
     * GetCheckoutData
     */
    public function getCheckoutData()
    {
        $actionUrl = $this->request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->request->getActionName();

        if ($actionUrl == 'checkout/index/index' || $actionUrl == 'firecheckout/index/index' || $actionUrl=='onestepcheckout/index/index') {
            $summary = $this->newSection('Proceed', 'proceed.phtml');

            if ($summary) {
                $cartItemQC = $this->checkoutsession->getQuote();
                $summary->setQuote($cartItemQC);
                $summary->toHtml();
            }
        }
    }

    /**
     * GetOrderData
     */
    public function getOrderData()
    {
        $lastRealOrder = $this->checkoutsession->getLastRealOrder();
        $lastOrderId = $lastRealOrder->getId();
        $currentPath = $this->request->getModuleName() .
            DIRECTORY_SEPARATOR . $this->request->getControllerName() .
            DIRECTORY_SEPARATOR . $this->request->getActionName();
        $checkoutPath = ['checkout/onepage/success'];
        $modifyPath = trim($this->helperData->getCheckoutSuccessLandingPage());
        if (strlen($modifyPath)) {
            $checkoutPath = array_merge($checkoutPath, explode(",", $modifyPath));
        }

        if (!in_array($currentPath, $checkoutPath) || !$lastOrderId) {
            return;
        }

        $summary = $this->newSection('Summary', 'summary.phtml');
        if ($summary) {
            $orderData = $this->orderRepo->get($lastOrderId);
            $summary->setOrder($orderData);
            $summary->toHtml();
        }
    }
}
