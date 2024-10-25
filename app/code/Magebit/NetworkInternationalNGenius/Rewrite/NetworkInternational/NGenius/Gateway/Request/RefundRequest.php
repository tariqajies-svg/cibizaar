<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\Rewrite\NetworkInternational\NGenius\Gateway\Request;

use Magebit\NetworkInternationalNGenius\Helper\Config as ExtendedConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use NetworkInternational\NGenius\Gateway\Config\Config;
use NetworkInternational\NGenius\Gateway\Request\RefundRequest as RefundRequestParent;
use NetworkInternational\NGenius\Gateway\Request\TokenRequest;
use NetworkInternational\NGenius\Model\CoreFactory;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;

class RefundRequest extends RefundRequestParent
{
    /**
     * @param Config $config
     * @param TokenRequest $tokenRequest
     * @param StoreManagerInterface $storeManager
     * @param CoreFactory $coreFactory
     * @param OrderInterface $orderInterface
     * @param ExtendedConfig $extendedConfig
     */
    public function __construct(
        Config $config,
        TokenRequest $tokenRequest,
        StoreManagerInterface $storeManager,
        CoreFactory $coreFactory,
        OrderInterface $orderInterface,
        protected readonly ExtendedConfig $extendedConfig
    ) {
        parent::__construct($config, $tokenRequest, $storeManager, $coreFactory, $orderInterface);
    }

    /**
     * Builds ENV request
     *
     * Types are not specified same as parent method.
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment   = $paymentDO->getPayment();
        $order     = $paymentDO->getOrder();
        $storeId   = (int)$order->getStoreId();

        $paymentResult = json_decode($payment->getAdditionalInformation('paymentResult'));

        $transactionId  = $paymentResult->reference;
        $orderReference = $paymentResult->orderReference;

        if (!$transactionId) {
            throw new LocalizedException(__('No capture transaction to proceed refund.'));
        }

        $incrementId = $order->getOrderIncrementId();

        $order_details = $this->_orderInterface->loadByIncrementId($incrementId);

        $token = $this->tokenRequest->getAccessToken($storeId);
        list($refund_url, $method, $error) = $this->getRefundUrl($token, $orderReference);

        $amount = $this->formatPrice(SubjectReader::readAmount($buildSubject)) * 100;

        /**
         * Converting amount into base currency
         */
        if ($this->extendedConfig->isPaymentNgeniusOnlineBaseCurrency($storeId)) {
            $currencyCode = $order_details->getBaseCurrencyCode();
        } else {
            $currencyCode = $order_details->getOrderCurrencyCode();
        }

        ValueFormatter::formatCurrencyAmount($currencyCode, $amount);

        if ($error) {
            throw new LocalizedException(__($error));
        }

        if ($this->config->isComplete($storeId)) {
            return [
                'token'   => $token,
                'request' => [
                    'data'   => [
                        'amount' => [
                            'currencyCode' => $currencyCode,
                            'value'        => $amount
                        ],
                        'merchantDefinedData' => [
                            'pluginName' => 'magento-2',
                            'pluginVersion' => '1.1.3'
                        ]
                    ],
                    'method' => $method,
                    'uri'    => $refund_url
                ]
            ];
        } else {
            throw new LocalizedException(__('Invalid configuration.'));
        }
    }
}
