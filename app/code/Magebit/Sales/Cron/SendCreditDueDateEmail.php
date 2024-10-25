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
namespace Magebit\Sales\Cron;

use Magebit\Sales\Model\Order\Email\Sender\CreditExpirationSender;
use Magebit\Sales\Model\Order\Email\Sender\CreditReminderSender;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

class SendCreditDueDateEmail
{
    public const DUE_DATES_CONFIG = 'sales_email/credit_reminder_email_days/days';
    public const EMAIL_SENDING_ENABLED = 'sales_email/credit_reminder_email_days/enabled';
    public function __construct(
        private readonly CollectionFactory $orderCollection,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Serialize $serialize,
        private readonly CreditReminderSender $creditReminderSender,
        private readonly CreditExpirationSender $creditExpirationSender
    ) {
    }

    public function execute(): void
    {
        $current_date = date('Y-m-d');

        $collection = $this->orderCollection->create();
        $collection->addAttributeToFilter('credit_due_date', ['gteq'=> $current_date]);
        $configDays = [];
        $emailSendingEnabled = [];

        if ($orders = $collection->getItems()) {
            foreach ($orders as $order) {
                $storeId = $order->getStoreId();

                // Check if email sending is enabled
                if (!isset($emailSendingEnabled[$storeId])) {
                    $emailSendingEnabled[$storeId] = $this->scopeConfig->getValue(
                        self::EMAIL_SENDING_ENABLED,
                        ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                }

                // Do nothing if disabled
                if (!$emailSendingEnabled[$storeId]) {
                    return;
                }

                // Get day configuration
                if (!isset($configDays[$storeId])) {
                    $configDays[$storeId] = $this->scopeConfig->getValue(
                        self::DUE_DATES_CONFIG,
                        ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                }

                foreach ($this->serialize->unserialize($configDays[$storeId]) as $configDay) {
                    // current date + config day to get expiration day
                    $expirationDate = strtotime('+' . $configDay['value'] . 'days');
                    // Check if admin selected date is equal to calculated expiration date
                    if ($order->getData('credit_due_date') === date('Y-m-d', $expirationDate)) {
                        $this->creditReminderSender->send($order, $configDay['value']);
                        break;
                    }
                }

                if ($order->getData('credit_due_date') === $current_date) {
                    $this->creditExpirationSender->send($order);
                }
            }
        }
    }
}
