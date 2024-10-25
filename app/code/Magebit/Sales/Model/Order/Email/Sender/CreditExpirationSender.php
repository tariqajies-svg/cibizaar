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
use Magebit\Sales\Model\Order\Email\Container\CreditExpirationIdentity;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Store\Model\App\Emulation;
use Psr\Log\LoggerInterface;

class CreditExpirationSender extends NotifySender
{
    /**
     * @param Template $templateContainer
     * @param CreditExpirationIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param Emulation $appEmulation
     */
    public function __construct(
        Template $templateContainer,
        CreditExpirationIdentity $identityContainer,
        SenderBuilderFactory $senderBuilderFactory,
        LoggerInterface $logger,
        Renderer $addressRenderer,
        private readonly Emulation $appEmulation
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @return bool
     */
    public function send(Order $order, bool $notify = true): bool
    {
        $this->identityContainer->setStore($order->getStore());
        $this->appEmulation->startEnvironmentEmulation($order->getStoreId(), Area::AREA_FRONTEND, true);
        $transport = [
            'email' => $order->getCustomerEmail(),
            'name' => $order->getCustomerName(),
            'invoice_number' => $order->getIncrementId()
        ];

        $transportObject = new DataObject($transport);
        $this->appEmulation->stopEnvironmentEmulation();
        $this->templateContainer->setTemplateVars($transportObject->getData());

        return $this->checkAndSend($order, $notify);
    }
}
