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
namespace Magebit\Sales\Model\Order\Email\Sender;

use IntlDateFormatter;
use Magebit\Sales\Model\Order\Email\Container\CreditreminderIdentity;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class CreditReminderSender extends NotifySender
{
    /**
     * @param Template $templateContainer
     * @param CreditreminderIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param Emulation $appEmulation
     * @param TimezoneInterface $localeDate
     * @param Data $priceHelper
     */
    public function __construct(
        Template $templateContainer,
        CreditreminderIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        private readonly Emulation $appEmulation,
        private readonly TimezoneInterface $localeDate,
        private readonly Data $priceHelper
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @param string $days
     * @param bool $notify
     * @return bool
     */
    public function send(Order $order, string $days, bool $notify = true): bool
    {
        $this->identityContainer->setStore($order->getStore());
        $this->appEmulation->startEnvironmentEmulation($order->getStoreId(), Area::AREA_FRONTEND, true);
        $transport = [
            'email' => $order->getCustomerEmail(),
            'name' => $order->getCustomerName(),
            'invoice_number' => $order->getIncrementId(),
            'due_date' => $this->getDateFormatted($order->getCreditDueDate()),
            'due_day' => $days,
            'outstanding_balance' => $this->priceHelper->currencyByStore($order->getGrandTotal(), $order->getStoreId(), true, false)
        ];

        $transportObject = new DataObject($transport);
        $this->appEmulation->stopEnvironmentEmulation();
        $this->templateContainer->setTemplateVars($transportObject->getData());

        return $this->checkAndSend($order, $notify);
    }

    /**
     * @param string $createdAt
     * @return string
     */
    public function getDateFormatted(string $createdAt): string
    {
        return $this->localeDate->formatDateTime(
            $createdAt,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            null,
            null,
            'dd/MM/yyyy'
        );
    }
}
