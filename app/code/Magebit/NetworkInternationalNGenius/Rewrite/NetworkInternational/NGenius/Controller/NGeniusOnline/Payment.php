<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\Rewrite\NetworkInternational\NGenius\Controller\NGeniusOnline;

use Magebit\NetworkInternationalNGenius\Helper\Config as ExtendedConfig;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use NetworkInternational\NGenius\Controller\NGeniusOnline\Payment as PaymentParent;
use NetworkInternational\NGenius\Gateway\Config\Config;
use NetworkInternational\NGenius\Gateway\Http\Client\TransactionFetch;
use NetworkInternational\NGenius\Gateway\Http\TransferFactory;
use NetworkInternational\NGenius\Gateway\Request\TokenRequest;
use NetworkInternational\NGenius\Model\CoreFactory;
use Psr\Log\LoggerInterface;

/**
 * We are overriding Parent class to change currency of all payments.
 * Payments can be processed in base currency.
 */
class Payment extends PaymentParent
{

    /**
     * @var Product
     */
    private Product $productCollection;

    /**
     * @var string
     */
    private string $errorMessage = 'There is an error with the payment';

    /**
     * @param ManagerInterface $messageManager
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     * @param Data $checkoutHelper
     * @param Config $config
     * @param TokenRequest $tokenRequest
     * @param StoreManagerInterface $storeManager
     * @param TransferFactory $transferFactory
     * @param TransactionFetch $transaction
     * @param CoreFactory $coreFactory
     * @param BuilderInterface $transactionBuilder
     * @param ResultFactory $resultRedirect
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceSender $invoiceSender
     * @param OrderSender $orderSender
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Product $productCollection
     * @param SerializerInterface $serializer
     * @param Builder $_transactionBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param ExtendedConfig $extendedConfig
     */
    public function __construct(
        ManagerInterface $messageManager,
        PageFactory $pageFactory,
        RequestInterface $request,
        Data $checkoutHelper,
        Config $config,
        TokenRequest $tokenRequest,
        StoreManagerInterface $storeManager,
        TransferFactory $transferFactory,
        TransactionFetch $transaction,
        CoreFactory $coreFactory,
        BuilderInterface $transactionBuilder,
        ResultFactory $resultRedirect,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender,
        OrderSender $orderSender,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        Session $checkoutSession,
        Product $productCollection,
        SerializerInterface $serializer,
        Builder $_transactionBuilder,
        OrderRepositoryInterface $orderRepository,
        protected readonly ExtendedConfig $extendedConfig
    ) {
        parent::__construct(
            $messageManager,
            $pageFactory,
            $request,
            $checkoutHelper,
            $config,
            $tokenRequest,
            $storeManager,
            $transferFactory,
            $transaction,
            $coreFactory,
            $transactionBuilder,
            $resultRedirect,
            $invoiceService,
            $transactionFactory,
            $invoiceSender,
            $orderSender,
            $orderFactory,
            $logger,
            $checkoutSession,
            $productCollection,
            $serializer,
            $_transactionBuilder,
            $orderRepository
        );

        $this->productCollection = $productCollection;
    }

    /**
     * Magento order capturing
     *
     * Types are not specified same as parent method.
     *
     * @param Order $order
     * @param array $paymentResult
     * @param string $paymentId
     * @param string $action
     * @param array $dataTable
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCapturePayment(
        Order $order,
        array $paymentResult,
        string $paymentId,
        string $action,
        array $dataTable
    ): array {
        if ($this->ngeniusState != self::NGENIUS_FAILED) {
            if ($this->ngeniusState != self::NGENIUS_STARTED) {
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING)->save();
                $this->orderSender->send($order, true);

                if ($action === "AUTH") {
                    $this->orderAuthorize($order, $paymentResult, $paymentId);
                } elseif ($action === "SALE" || $action === 'PURCHASE') {
                    $dataTable['captured_amt'] = $this->orderSale($order, $paymentResult, $paymentId);
                }
                $dataTable['status'] = $order->getStatus();
            } else {
                $dataTable['status'] = Order::STATE_PENDING_PAYMENT;
            }
        } else {
            $storeId = (int)$this->storeManager->getStore()->getId();

            // Authorisation has failed - cancel order
            // Reverse reserved stock
            $this->error = true;
            $this->errorMessage = 'Result Code: ' . ($paymentResult['authResponse']['resultCode'] ?? 'FAILED')
                . ' Reason: ' . ($paymentResult['authResponse']['resultMessage'] ?? 'Unknown');
            $this->checkoutSession->restoreQuote();

            $payment = $order->getPayment();

            /**
             * Converting amount into base currency
             */
            if ($this->extendedConfig->isPaymentNgeniusOnlineBaseCurrency($storeId)) {
                $formattedPrice = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());
            } else {
                $formattedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
            }

            $paymentData = [
                'Card Type' => $paymentResult['paymentMethod']['name'] ?? '',
                'Card Number' => $paymentResult['paymentMethod']['pan'] ?? '',
                'Amount' => $formattedPrice
            ];

            $trans = $this->_transactionBuilder;

            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $paymentData]
                )
                ->setFailSafe(true)
                // Build method creates the transaction and returns the object
                ->build(TransactionInterface::TYPE_CAPTURE);

            $payment->addTransactionCommentsToOrder(
                $transaction,
                $this->errorMessage
            );

            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();

            $transaction->save()->getTransactionId();
            $this->updateInvoice($order, false);

            $payment->setAdditionalInformation(['raw_details_info' => json_encode($paymentResult)]);

            if ($this->config->getCustomFailedOrderStatus($storeId) != null) {
                $status = $this->config->getCustomFailedOrderStatus($storeId);
            } else {
                $status = Order::STATE_CLOSED;
            }

            if ($this->config->getCustomFailedOrderState($storeId) != null) {
                $state = $this->config->getCustomFailedOrderState($storeId);
            } else {
                $state = Order::STATE_CLOSED;
            }

            $dataTable['status'] = $status;

            $order->cancel()->save();

            $order->setState($state);
            $order->setStatus($status);
            $order->save();

            $order->addStatusHistoryComment('The payment on order has failed.')
                ->setIsCustomerNotified(false)->save();
        }

        return $dataTable;
    }

    /**
     * Order Authorize.
     *
     * Types are not specified same as parent method.
     *
     * @param Order $order
     * @param array $paymentResult
     * @param string $paymentId
     *
     * @return null
     * @throws Exception
     */
    public function orderAuthorize(Order $order, array $paymentResult, string $paymentId)
    {
        if ($this->ngeniusState == self::NGENIUS_AUTHORISED) {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentId);
            $payment->setTransactionId($paymentId);
            $payment->setAdditionalInformation(['paymentResult' => json_encode($paymentResult)]);
            $payment->setIsTransactionClosed(false);

            $storeId = (int)$order->getStoreId();

            /**
             * Converting amount into base currency
             */
            if ($this->extendedConfig->isPaymentNgeniusOnlineBaseCurrency($storeId)) {
                $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getBaseGrandTotal());
            } else {
                $formatedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());
            }

            $paymentData = [
                'Card Type' => $paymentResult['paymentMethod']['name'] ?? '',
                'Card Number' => $paymentResult['paymentMethod']['pan'] ?? '',
                'Amount' => $formatedPrice
            ];

            $transactionBuilder = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => $paymentData]
                )->setAdditionalInformation(
                    ['paymentResult' => json_encode($paymentResult)]
                )
                ->setFailSafe(true)
                ->build(
                    Transaction::TYPE_AUTH
                );

            $payment->addTransactionCommentsToOrder($transactionBuilder, null);
            $payment->setParentTransactionId(null);
            $payment->save();

            $message = 'The payment has been approved and the authorized amount is ' . $formatedPrice;

            $status = Order::STATE_PROCESSING;

            $this->updateOrderStatus($order, $status, $message);
        }
    }

    /**
     * Order Sale.
     *
     * Types are not specified same as parent method.
     *
     * @param Order $order
     * @param array  $paymentResult
     * @param string $paymentId
     *
     * @return null|float
     * @throws Exception
     */
    public function orderSale($order, $paymentResult, $paymentId)
    {
        if ($this->ngeniusState === self::NGENIUS_CAPTURED || $this->ngeniusState === self::NGENIUS_PURCHASED) {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentId);
            $payment->setTransactionId($paymentId);
            $payment->setAdditionalInformation(['paymentResult' => json_encode($paymentResult)]);
            $payment->setIsTransactionClosed(false);

            $storeId = (int)$order->getStoreId();

            /**
             * Converting amount into base currency
             */
            if ($this->extendedConfig->isPaymentNgeniusOnlineBaseCurrency($storeId)) {
                $grandTotal = $order->getBaseGrandTotal();
            } else {
                $grandTotal = $order->getGrandTotal();
            }

            $formatedPrice = $order->getBaseCurrency()->formatTxt($grandTotal);

            $paymentData = [
                'Card Type'   => $paymentResult['paymentMethod']['name'] ?? '',
                'Card Number' => $paymentResult['paymentMethod']['pan'] ?? '',
                'Amount'      => $formatedPrice
            ];

            $transactionId = $paymentResult['reference'];

            $transactionBuilder = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setAdditionalInformation(
                    [Transaction::RAW_DETAILS => (array)$paymentData]
                )
                ->setAdditionalInformation(
                    ['paymentResult' => json_encode($paymentResult)]
                )
                ->setFailSafe(true)
                ->build(
                    Transaction::TYPE_CAPTURE
                );

            $payment->addTransactionCommentsToOrder($transactionBuilder, null);
            $payment->setParentTransactionId(null);
            $payment->save();

            $message = 'The payment has been approved and the captured amount is ' . $formatedPrice;

            if ($order->canShip()) {
                $status = Order::STATE_PROCESSING;
            } else {
                $status = Order::STATE_COMPLETE;
            }

            $this->updateOrderStatus($order, $status, $message);

            $this->updateInvoice($order, true, $transactionId);

            return $grandTotal;
        }
    }

    /**
     * Order Status Updater
     *
     * @param Order $order
     * @param ?string $status
     * @param string $message
     * @return void
     * @throws NoSuchEntityException
     */
    private function updateOrderStatus(Order $order, ?string $status, string $message): void
    {
        //Check For Custom Order Status on Payment Complete
        $storeId = (int)$this->storeManager->getStore()->getId();

        if ($this->config->getCustomSuccessOrderStatus($storeId) != null) {
            $status = $this->config->getCustomSuccessOrderStatus($storeId);
        }

        if ($this->config->getCustomSuccessOrderState($storeId) != null) {
            $order->setState($this->config->getCustomSuccessOrderState($storeId));
        }

        $order->addStatusToHistory($status, $message, true);
        $order->save();
    }
}
