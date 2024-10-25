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

use Aheadworks\RequisitionLists\Api\RequisitionListItemRepositoryInterface;
use Aheadworks\RequisitionLists\Api\RequisitionListRepositoryInterface;
use Aheadworks\RequisitionLists\Controller\RList\UpdateItemQty as UpdateItemQtyOrigin;
use Aheadworks\RequisitionLists\Model\Product\DetailProvider\Pool;
use Aheadworks\RequisitionLists\Model\RequisitionList\Provider;
use Aheadworks\RequisitionLists\Model\ResourceModel\RequisitionList\Item\CollectionFactory;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\Result\Redirect;

class UpdateItemQty extends UpdateItemQtyOrigin
{
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
            $pool
        );
        $this->pool = $pool;
    }

    /**
     * @param $resultRedirect
     * @return Redirect
     */
    protected function update($resultRedirect): Redirect
    {
        $itemsQty = $this->getItems();

        $count = 0;
        $failed = [];
        foreach ($itemsQty as $itemId => $qty) {
            try {
                if (!$itemId || !$qty) {
                    continue;
                }
                $item = $this->requisitionListItemRepository->get($itemId);
                $provider = $this->pool->getProvider($item->getData());
                if (!$provider->getQtyIsSalable((float)$qty)) {
                    $failed[] = $item->getProductName();
                    continue;
                }
                $item->setProductQty((int)$qty);
                $this->requisitionListItemRepository->save($item);
                $count++;
            } catch (Exception $e) {
                continue;
            }
        }

        $this->messageManager->addSuccessMessage(
            __(
                '%1 item(s) have been updated in "%2" list.',
                $count,
                $this->getCurrentRequisitionListName()
            )
        );

        if (count($failed)) {
            $this->messageManager->addErrorMessage(
                __(
                    'The requested qty is not available for these products: %1.',
                    implode(',', $failed)
                )
            );
        }

        return $resultRedirect;
    }
}
