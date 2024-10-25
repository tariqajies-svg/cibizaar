<?php
/**
 * This file is part of the Magebit_WhatsApp package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_WhatsApp
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\WhatsApp\Model\TwoFa;

use Magebit\TwoFa\Service\Methods\AbstractMethod;
use Magebit\WhatsApp\Model\Client;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

class WhatsApp extends AbstractMethod
{
    public const CODE = 'whatsapp';
    private const ENABLED_XML_PATH = 'whatsapp/twofa/enable';
    private const TEMPLATE_NAME_XML_PATH = 'whatsapp/twofa/template_name';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param Client $client
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        private readonly Client $client
    ) {
        parent::__construct($scopeConfig, $customerRepository);
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('WhatsApp');
    }

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function isAllowed(CustomerInterface $customer): bool
    {
        if (!$this->scopeConfig->getValue(self::ENABLED_XML_PATH, ScopeInterface::SCOPE_STORE) || !$this->getTemplate()) {
            return false;
        }

        return !!$customer->getExtensionAttributes()->getCustomerTelephone();
    }

    /**
     * @return string|null
     */
    private function getTemplate(): ?string
    {
        return $this->scopeConfig->getValue(self::TEMPLATE_NAME_XML_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param CustomerInterface $customer
     * @return AbstractMethod
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function send(CustomerInterface $customer): AbstractMethod
    {
        $code = $this->attachCode($customer);
        $phone = $customer->getExtensionAttributes()->getCustomerTelephone();

        try {
            $this->client->sendMessage(
                $phone,
                $this->getTemplate(),
                [
                    [
                        'type' => 'text',
                        'text' => $code
                    ]
                ]
            );
        } catch (\Exception $exception) {
            throw new LocalizedException(__('Something went wrong. Please try different method.'));
        }

        return $this;
    }
}
