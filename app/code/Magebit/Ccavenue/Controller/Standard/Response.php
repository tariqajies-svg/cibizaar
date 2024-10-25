<?php
/**
 * This file is part of the Magebit_Ccavenue package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
namespace Magebit\Ccavenue\Controller\Standard;

use Infibeam\Ccavenue\Controller\Standard\Response as ResponseDefault;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Response extends ResponseDefault
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_logger->info('customer returned to website.');
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');
        $message = '';

        try {
            $paymentMethod = $this->getPaymentMethod();
            $params = $this->getRequest()->getParams();
            $response = $paymentMethod->decryptResponse($params);

            if (isset($response)) {
                $resOrderId = $response['order_id'];
                $resAmount = round($response['mer_amount'], 2);
                $resCurrency = $response['currency'];

                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
                $order = $this->getOrderById($resOrderId);
                $payment = $order->getPayment();

                $reqOrderId = $order->getIncrementId();
                $reqAmount = round($order->getGrandTotal(), 2);
                $reqCurrency = $order->getOrderCurrencyCode();

                $this->_logger->info('Response Order Status : ' . $response['order_status']);
                $this->_logger->info($reqOrderId . '==' . $resOrderId . ' | ' . $reqAmount . '==' . $resAmount . ' | ' . $reqCurrency . '==' . $resCurrency);

                if ($reqOrderId == $resOrderId && $reqAmount == $resAmount && $reqCurrency == $resCurrency) {
                    $paymentMethod->postProcessing($order, $payment, $response);
                    if ($response['order_status'] == 'Success') {
                        if ($paymentMethod->getConfigData('payment_auto_invoice')) {
                            $this->_createInvoice($order->getId());
                        }

                        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
                    } elseif ($response['order_status'] == "Aborted") {
                        $message = __('Your order has been cancelled.');
                    } elseif ($response['order_status'] == "Failure") {
                        $message = __('Payment has been failed. Please try again.');
                    } elseif ($response['order_status'] == "Declined") {
                        $message = __('Payment has been declined. Please try again.');
                    } else {
                        $message = __('Payment has been failed. Please try again or choose a different payment option.');
                    }
                } else {
                    $payment->setTransactionId($response['tracking_id']);
                    $payment->setTransactionAdditionalInfo('status_message', $response['status_message']);
                    $payment->setTransactionAdditionalInfo('payment_mode', $response['payment_mode']);
                    $order->setState('fraud')->setStatus('fraud');
                    $order->addStatusHistoryComment('Txn Id: '
                        . $response['tracking_id'] . ' | '
                        . $reqOrderId . ' == '
                        . $resOrderId . ' | '
                        . $reqAmount . ' == '
                        . $resAmount . ' | '
                        . $reqCurrency . ' == '
                        . $resCurrency, false);
                    $order->save();
                    $payment->setIsTransactionClosed(0);
                    $payment->place();
                    $this->messageManager->addErrorMessage(__('Security error : Illegal access detected. Please contact administrator.'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));
        }

        if ($message!='') {
            $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/cart');
            $this->_cancelPayment($message);
            $this->messageManager->addErrorMessage($message);
            $this->_logger->info($message);
        }
        $this->getResponse()->setRedirect($returnUrl);
    }
}
