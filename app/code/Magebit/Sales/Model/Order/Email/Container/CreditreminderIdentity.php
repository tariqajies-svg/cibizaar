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

namespace Magebit\Sales\Model\Order\Email\Container;

use Magento\Sales\Model\Order\Email\Container\Container;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

class CreditreminderIdentity extends Container implements IdentityInterface
{
    public const XML_PATH_EMAIL_COPY_METHOD = 'sales_email/credit_reminder_email_days/copy_method';
    public const XML_PATH_EMAIL_COPY_TO = 'sales_email/credit_reminder_email_days/copy_to';
    public const XML_PATH_EMAIL_IDENTITY = 'sales_email/credit_reminder_email_days/identity';
    public const XML_PATH_EMAIL_GUEST_TEMPLATE = 'sales_email/credit_reminder_email_days/template';
    public const XML_PATH_EMAIL_TEMPLATE = 'sales_email/credit_reminder_email_days/template';
    public const XML_PATH_EMAIL_ENABLED = 'sales_email/credit_reminder_email_days/enabled';

    /**
     * Is email enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo(): bool|array
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            return array_map('trim', explode(',', $data));
        }
        return false;
    }

    /**
     * Return email copy method
     *
     * @return mixed
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * Return guest template id
     *
     * @return mixed
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * Return email identity
     *
     * @return mixed
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
