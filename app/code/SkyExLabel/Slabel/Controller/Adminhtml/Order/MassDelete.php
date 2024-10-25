<?php

namespace SkyExLabel\Slabel\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countDeleteOrder = 0;
        $model = $this->_objectManager->create('Magento\Sales\Model\Order');
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            $loadedOrder = $model->load($order->getEntityId());
            // $loadedOrder->delete();

            $countDeleteOrder++;
        }
        $countNonDeleteOrder = $collection->count() - $countDeleteOrder;

        if ($countNonDeleteOrder && $countDeleteOrder) {
            $this->messageManager->addError(__('%1 order(s) were not deleted.', $countNonDeleteOrder));
        } elseif ($countNonDeleteOrder) {
            $this->messageManager->addError(__('No order(s) were deleted.'));
        }

        if ($countDeleteOrder) {
            $this->messageManager->addSuccess(__('You have deleted %1 order(s).', $countDeleteOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}