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

namespace Magedelight\Ga4\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magedelight\Ga4\Helper\Data;

class Item extends \Magento\Framework\App\Action\Action
{

    /** @var ResultJsonFactory */
    private $resultJsonFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductRepositoryInterface */
    private $categoryFactory;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ResultJsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        Data $helper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->helper = $helper;
 
        return parent::__construct($context);
    }

    public function execute()
    {

        $data = $this->getRequest()->getParams();
        $product = null;
        $ecommerceData = [];

        $promoId           = (isset($data['promoId'])) ? $data['promoId'] : null;
        $promoName         = (isset($data['promoName'])) ? $data['promoName'] : null;
        $promoCreativeName = (isset($data['promoCreativeName'])) ? $data['promoCreativeName'] : null;
        $promoCreativeSlot = (isset($data['promoCreativeSlot'])) ? $data['promoCreativeSlot'] : null;
        $event             = (isset($data['event'])) ? $data['event'] : null;

        $resultJson = $this->resultJsonFactory->create();
        if (isset($data['pid']) && $data['pid']!="") {
            $product[] = $this->getProduct($data['pid']);
        } elseif (isset($data['cid']) && $data['cid']!="") {
            $product[] = $this->getCategoryItem($data['cid']);
        }
        if (!empty($product)) {
            $ecommerceData['event'] = $event;
            $ecommerceData['ecommerce'] = [
                                            'creative_name'=> $promoCreativeName,
                                            'creative_slot'=> $promoCreativeSlot,
                                            'promotion_id'=>  $promoId ,
                                            'promotion_name'=> $promoName,
                                            'items'=>$product
                                        ];
            $this->helper->setTagManagerReportData($ecommerceData);
            $resultJson->setData(["eventData" => $ecommerceData,"status"=>1]);
        } else {
            $resultJson->setData(["status"=>0]);
        }
        
        return $resultJson;
    }

    private function getProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $item = $this->getItemArray($product);
            return $item;
        } catch (Exception $e) {
        }
    }

    private function getCategoryItem($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        $product =  $category->getProductCollection()->addAttributeToSelect('*')->getFirstItem();
        $item = $this->getItemArray($product);
        return $item;
    }

    private function getItemArray($product)
    {
        $itemDetails = [];

        if ($product->getId()) {
            $itemDetails['item_id'] = $this->helper->getProductIdentifier($product);
            $itemDetails['item_name'] = $product->getName();
            $itemDetails['affiliation'] = $this->helper->getNameOfAffilation();
            if ($this->helper->isBrandEnabled()) {
                $itemDetails['item_brand'] = $this->helper->getBrandData($product);
            }
            $catgeoryId = $this->helper->getCatIds($product->getCategoryIds());

            if (!empty($catgeoryId)) {
                $j=1;
                for ($i=0; $i < count($catgeoryId); $i++) {
                    if ($j>1) {
                        $itemDetails['item_category'.$i] = $catgeoryId[$i];
                    } else {
                        $itemDetails['item_category'] = $catgeoryId[$i];
                    }
                    $j++;
                }
                $itemDetails['item_list_name'] = implode('/', $catgeoryId);
                $productCategoryIds = $product->getCategoryIds();
                $itemDetails['item_list_id'] = count($productCategoryIds) ? $productCategoryIds[0] : null;
            }
            $itemDetails['price'] = $this->helper->getPriceFormat($product);
            $itemDetails['quantity'] = 1;
        }

        return $itemDetails;
    }
}
