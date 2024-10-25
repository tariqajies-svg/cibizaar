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

namespace Magebit\TwoFa\Service\Methods;

use Magebit\TwoFa\Api\Data\TwoFaAttributesInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractMethod
{
    protected const NUMBER_ALPHABET = '0123456789';
    protected const NUMBER_AND_LETTER_ALPHABET = 'K0lA1MB2CQN3ORD4SPEZV5F6GW7YH8IX9J';
    protected const DEFAULT_CODE_LENGTH = 5;
    protected const CODE_LENGTH_XML = 'twofa/code/length';
    protected const CODE_ALLOW_LETTERS_XML = 'twofa/code/allow_letters';
    protected const CODE_EXPIRES_IN_XML = 'twofa/code/expiration';

    public const CODE = 'method';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('2FA Method');
    }

    /**
     * @param CustomerInterface $customer
     * @return $this
     */
    public function send(CustomerInterface $customer): self
    {
        $this->attachCode($customer);

        return $this;
    }

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function isAllowed(CustomerInterface $customer): bool
    {
        return true;
    }

    /**
     * Get Code expiration in seconds
     *
     * @return int
     */
    public function getExpiresIn(): int
    {
        return (int) $this->scopeConfig->getValue(self::CODE_EXPIRES_IN_XML, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param CustomerInterface $customer
     * @return string
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    protected function attachCode(CustomerInterface $customer): string
    {
        $code = $this->generateCode();
        $customer->setCustomAttribute(TwoFaAttributesInterface::CODE, $code);
        $customer->setCustomAttribute(TwoFaAttributesInterface::CREATED_AT, date("Y-m-d H:i:s", time()));
        $this->customerRepository->save($customer);

        return $code;
    }

    /**
     * @return string
     */
    protected function generateCode(): string
    {
        $length = (int) $this->scopeConfig->getValue(self::CODE_LENGTH_XML, ScopeInterface::SCOPE_STORE) ?? self::DEFAULT_CODE_LENGTH;
        $alphabet = !!$this->scopeConfig->getValue(self::CODE_ALLOW_LETTERS_XML, ScopeInterface::SCOPE_STORE) ?
                self::NUMBER_AND_LETTER_ALPHABET : self::NUMBER_ALPHABET;
        $alphabetLength = strlen($alphabet);
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $pos = rand(0, $alphabetLength - 1);
            $code .= substr($alphabet, $pos, 1);
        }

        return $code;
    }
}
