<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\Sales\Controller\Adminhtml\Order;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Ajax extends Action
{
    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param OrderRepositoryInterface $repository
     * @param OrderPaymentRepositoryInterface $orderPaymentRepository
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly OrderRepositoryInterface $repository,
        private readonly OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();
        $request = $this->getRequest();

        if ($request->isAjax()) {
            try {
                /** @var Order $order */
                $order = $this->repository->get($request->getParam('orderId'));

                $params = $request->getParams();

                if (isset($params['creditDueDate'])) {
                    $order->setData('credit_due_date', $request->getParam('creditDueDate'));
                    $this->repository->save($order);
                }

                if (isset($params['poNumber'])) {
                    $payment = $order->getPayment();
                    $oldPoNumber = $payment->getPoNumber();

                    // Save new PO number
                    $payment->setPoNumber($request->getParam('poNumber'));
                    $this->orderPaymentRepository->save($payment);

                    $message = $oldPoNumber ?
                        __('PO number changed')
                        : __('PO number added');

                    $order->addStatusToHistory(
                        $order->getStatus(),
                        $message . ': ' . ($oldPoNumber ? $oldPoNumber . ' > ' : '') . $payment->getPoNumber()
                    );
                    $this->repository->save($order);
                }

            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $result->setData(['success' => false]);
            }
        }

        return $result->setData(['success' => true]);
    }
}
