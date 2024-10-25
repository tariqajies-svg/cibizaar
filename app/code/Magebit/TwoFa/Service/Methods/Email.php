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

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

class Email extends AbstractMethod
{
    public const CODE = 'email';
    private const TEMPLATE = 'two_fa_code';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        private readonly TransportBuilder $transportBuilder
    ) {
        parent::__construct($scopeConfig, $customerRepository);
    }

    /**
     * @return Phrase
     */
    public function getLabel(): Phrase
    {
        return __('Email');
    }

    /**
     * @param CustomerInterface $customer
     * @return AbstractMethod
     * @throws InputException
     * @throws LocalizedException
     * @throws MailException
     * @throws InputMismatchException
     */
    public function send(CustomerInterface $customer): AbstractMethod
    {
        $code = $this->attachCode($customer);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier(self::TEMPLATE)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => 0
            ])
            ->setTemplateVars([
                'code' => $code,
                'customer_name' => $customer->getFirstname() . ' ' . $customer->getLastname(),

            ])->setFromByScope(
                'general'
            )->addTo($customer->getEmail())
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
