<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\Plugin\NetworkInternational\NGenius\Gateway\Request;

use Magebit\NetworkInternationalNGenius\Helper\Config;
use Magento\Sales\Model\Order;
use NetworkInternational\NGenius\Gateway\Request\PaymentRequest as PaymentRequestSubject;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;

class PaymentRequest
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected readonly Config $config
    ) {
    }

    /**
     * @param PaymentRequestSubject $subject
     * @param $result
     * @param Order $order
     * @param int $storeId
     * @param float $amount
     * @param string $action
     * @return mixed
     */
    public function afterGetBuildArray(
        PaymentRequestSubject $subject,
        $result,
        $order,
        $storeId,
        $amount,
        $action
    ) {
        $storeId = (int)$storeId;

        // Do not adjust currency, if config disabled
        if (!$this->config->isPaymentNgeniusOnlineBaseCurrency($storeId)) {
            return $result;
        }

        /**
         * Converting API request into base currency. Following same logic as original extension.
         * But adding formatPrice to fix issue with rounding
         * @see \NetworkInternational\NGenius\Block\Ngenius::getPaymentUrl
         * @see \NetworkInternational\NGenius\Gateway\Request\CaptureRequest::build
         */
        $grandTotal = $this->formatPrice((float)$order->getBaseGrandTotal()) * 100;
        $currencyCode = $order->getBaseCurrencyCode();

        /**
         * Formatting amount as original extension.
         * @see \NetworkInternational\NGenius\Gateway\Request\PaymentRequest::getBuildArray
         */
        ValueFormatter::formatCurrencyAmount($currencyCode, $grandTotal);

        $result['data']['amount']['value'] = (int)$grandTotal;
        $result['data']['amount']['currencyCode'] = $currencyCode;

        return $result;
    }

    /**
     * Format price to 0.00 format
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return number_format($price, 2, '.', '');
    }
}
