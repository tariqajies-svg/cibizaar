<?php
/**
 * This file is part of the Magebit_Customer package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\Customer\Plugin\Magento\Customer\Model;

use Magebit\Customer\Block\Widget\CustomerTelephone;
use Magento\Customer\Api\Data\CustomerInterface;

class Customer
{
    /**
     * @param \Magento\Customer\Model\Customer $subject
     * @param CustomerInterface $result
     * @return CustomerInterface
     */
    public function afterGetDataModel(
        \Magento\Customer\Model\Customer $subject,
        CustomerInterface $result
    ): CustomerInterface {
        $extensionAttributes = $result->getExtensionAttributes();
        $extensionAttributes->setCustomerTelephone($subject->getData(CustomerTelephone::ATTRIBUTE_CODE));
        $extensionAttributes->setSubscribedWhatsapp($subject->getData('subscribed_whatsapp'));
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
