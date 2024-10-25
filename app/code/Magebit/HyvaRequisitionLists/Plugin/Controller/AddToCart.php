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
namespace Magebit\HyvaRequisitionLists\Plugin\Controller;

use Aheadworks\RequisitionLists\Api\Data\RequisitionListItemInterface;
use Aheadworks\RequisitionLists\Api\RequisitionListRepositoryInterface;
use Aheadworks\RequisitionLists\Api\RequisitionListItemRepositoryInterface;
use Aheadworks\RequisitionLists\Model\Product\DetailProvider\Pool;
use Aheadworks\RequisitionLists\Model\RequisitionList\Provider;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Item\CollectionFactory;
use Aheadworks\RequisitionLists\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\RequisitionLists\Controller\RList\AddToCart as AddToCartOrigin;
use Magento\Framework\Controller\Result\Redirect;

class AddToCart extends AddToCartOrigin
{
    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $cartManager;

    /**
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var Pool
     */
    private Pool $pool;

    /**
     * @param Provider $provider
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ResponseFactory $responseFactory
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param PageFactory $pageFactory
     * @param RequisitionListItemRepositoryInterface $requisitionListItemRepository
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CartManagementInterface $cartManager
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param Pool $pool
     */
    public function __construct(
        Provider $provider,
        Context $context,
        CustomerSession $customerSession,
        ResponseFactory $responseFactory,
        RequisitionListRepositoryInterface $requisitionListRepository,
        PageFactory $pageFactory,
        RequisitionListItemRepositoryInterface $requisitionListItemRepository,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CartManagementInterface $cartManager,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository,
        Pool $pool
    ) {
        parent::__construct(
            $provider,
            $context,
            $customerSession,
            $responseFactory,
            $requisitionListRepository,
            $pageFactory,
            $requisitionListItemRepository,
            $filter,
            $collectionFactory,
            $cartManager,
            $checkoutSession,
            $cartRepository,
            $pool
        );
        $this->cartManager = $cartManager;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->pool = $pool;
    }

    /**
     * @param Redirect $resultRedirect
     * @return Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function update($resultRedirect): Redirect
    {
        $count = 0;
        $items = $this->getAvailableItems();
        if ($items) {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote->getId()) {
                $this->cartRepository->save($quote);
            }
            $count = $this->cartManager->addItemsToCart($items, $quote->getId());

            if ($count) {
                $resultRedirect->setPath('checkout/cart');
            }
        }

        $this->messageManager->addSuccessMessage(
            __(
                '%1 of %2 item(s) successfully added to the cart.',
                $count,
                count($this->getItems())
            )
        );

        return $resultRedirect;
    }

    /**
     * Retrieve available items
     *
     * @return RequisitionListItemInterface[]
     * @throws LocalizedException
     */
    private function getAvailableItems()
    {
        $availableItems = [];
        $items = $this->getItems();

        /** @var RequisitionListItemInterface $item */
        foreach ($items as $item) {
            try {
                $provider = $this->pool->getProvider($item->getData());
                // Converting product qty to float because otherwise it isn't possible to add to cart
                if ($provider->isAvailableForSite() && $provider->getQtyIsSalable((float)$item->getProductQty())) {
                    $availableItems[] = $item;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $availableItems;
    }
}
