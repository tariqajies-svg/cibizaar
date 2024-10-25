<?php
/**
 * This file is part of the Magebit_TwoFa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_TwoFa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\TwoFa\Service;

use Magebit\TwoFa\Api\Data\TwoFaAttributesInterface;
use Magebit\TwoFa\Service\Methods\AbstractMethod;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Store\Model\ScopeInterface;

class TwoFaService
{
    private const ENABLED_XML = 'twofa/general/enable';

    /**
     * @param Session $session
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param AbstractMethod[] $methods
     */
    public function __construct(
        private readonly Session $session,
        private readonly PhpCookieManager $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly array $methods = []
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return !!$this->scopeConfig->getValue(self::ENABLED_XML, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return AbstractMethod[]
     */
    public function getAllMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function getCustomerMethodsCodes(CustomerInterface $customer): array
    {
        $methods = [];

        foreach ($this->getAllMethods() as $method) {
            if ($method->isAllowed($customer)) {
                $methods[] = $method::CODE;
            }
        }

        return $methods;
    }

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function shouldBypassTwoFa(CustomerInterface $customer): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        $attribute = $customer->getCustomAttribute('disable_2fa');

        return $attribute && !!$attribute->getValue();
    }

    /**
     * @param string $email
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerByEmail(string $email): CustomerInterface
    {
        return $this->customerRepository->get($email);
    }

    /**
     * @param CustomerInterface $customer
     * @return void
     * @throws InputException
     * @throws FailureToSendException
     */
    public function loginUser(CustomerInterface $customer): void
    {
        $this->session->setCustomerDataAsLoggedIn($customer);
        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * @param string $code
     * @param string $methodCode
     * @param string $customerEmail
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateCode(string $code, string $methodCode, string $customerEmail): bool
    {
        $customer = $this->customerRepository->get($customerEmail);
        $method = $this->getMethodByCode($methodCode);
        $customerCode = $customer->getCustomAttribute(TwoFaAttributesInterface::CODE);
        if ($customerCode && $customerCode->getValue() === $code
            && $customer->getCustomAttribute(TwoFaAttributesInterface::CREATED_AT)) {
            $codeCreatedAt = $customer->getCustomAttribute(TwoFaAttributesInterface::CREATED_AT)->getValue();
            $expires = strtotime($codeCreatedAt) + $method->getExpiresIn();
            $timeNow = time();
            return $timeNow < $expires;
        }

        return false;
    }

    /**
     * @param string $methodCode
     * @param string $customerEmail
     * @return void
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processMethod(string $methodCode, string $customerEmail): void
    {
        $method = $this->getMethodByCode($methodCode);
        $customer = $this->customerRepository->get($customerEmail);

        $method->send($customer);
    }

    /**
     * @param string $methodCode
     * @return AbstractMethod
     * @throws InputException
     */
    private function getMethodByCode(string $methodCode): AbstractMethod
    {
        foreach ($this->getAllMethods() as $method) {
            if ($method::CODE === $methodCode) {
                return $method;
            }
        }

        throw new InputException(__('Could not find method (%1)', $methodCode));
    }
}
