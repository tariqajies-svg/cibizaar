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

namespace Magebit\Catalog\Override\Magento_Catalog\Catalog\Controller\Product\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Product\Compare\Add;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class AddToCompare extends Add
{
    private AddToCompareAvailability $compareAvailability;

    public function __construct(
        Context $context,
        ItemFactory $compareItemFactory,
        CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        Visitor $customerVisitor,
        ListCompare $catalogProductCompareList,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        ObjectManagerInterface $objectManager,
        AddToCompareAvailability $compareAvailability = null
    ) {
        $this->compareAvailability = $compareAvailability
            ?: $objectManager->get(AddToCompareAvailability::class);

        parent::__construct(
            $context,
            $compareItemFactory,
            $itemCollectionFactory,
            $customerSession,
            $customerVisitor,
            $catalogProductCompareList,
            $catalogSession,
            $storeManager,
            $formKeyValidator,
            $resultPageFactory,
            $productRepository,
            $compareAvailability
        );
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'success' => false
            ]);
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
                $result->setData([
                    'success' => false
                ]);
            }

            if ($product && $this->compareAvailability->isAvailableForCompare($product)) {
                $this->_catalogProductCompareList->addProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                $result->setData(
                    [
                        'success' => true
                    ]
                );
            }

            $this->_objectManager->get(Compare::class)->calculate();
        }

        return $result;
    }
}
