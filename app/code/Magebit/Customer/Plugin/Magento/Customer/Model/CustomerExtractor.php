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
use Magento\Customer\Model\CustomerExtractor as CustomerExtractorSubject;
use Magento\Framework\App\RequestInterface;

class CustomerExtractor
{
    /**
     * @param CustomerExtractorSubject $subject
     * @param CustomerInterface $result
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     *
     * @return CustomerInterface
     */
    public function afterExtract(
        CustomerExtractorSubject $subject,
        CustomerInterface $result,
        string $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ): CustomerInterface {
        $extensionAttributes = $result->getExtensionAttributes();
        $extensionAttributes->setCustomerTelephone($request->getParam(CustomerTelephone::ATTRIBUTE_CODE, false));
        $extensionAttributes->setSubscribedWhatsapp($request->getParam('subscribed_whatsapp', false) === 'on');
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
