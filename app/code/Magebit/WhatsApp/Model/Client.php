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

namespace Magebit\WhatsApp\Model;

use Magebit\WhatsApp\Model\Config\Source\WhatsAppMode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Client
{
    private const MODE_PATH_XML = 'whatsapp/general/mode';
    private const PROD_URL_PATH_XML = 'whatsapp/general/prod_url';
    private const TEST_URL_PATH_XML = 'whatsapp/general/test_url';
    private const TEST_API_KEY_PATH_XML = 'whatsapp/general/test_api_key';
    private const PROD_API_KEY_PATH_XML = 'whatsapp/general/prod_api_key';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Curl $curl,
        private readonly StoreManagerInterface $storeManager,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * @return Curl
     */
    public function getClient(): Curl
    {
        $apiKey = $this->getApiKey();

        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('x-api-key', $apiKey);

        return $this->curl;
    }

    /**
     * @param string $phoneNumber
     * @param string $templateName
     * @param array|null $bodyValues
     * @return void
     * @throws NoSuchEntityException
     */
    public function sendMessage(
        string $phoneNumber,
        string $templateName,
        ?array $bodyValues = null,
    ) {
        $store = $this->storeManager->getStore();
        $client = $this->getClient();
        $result = $client->post(
            $this->getUrl() . '/send',
            json_encode([
                'phone' => $phoneNumber,
                'type' => 'template',
                'language' => $store->getLocale() ?? 'en_US',
                'name' => $templateName,
                'body' => $bodyValues
            ])
        );
    }

    /**
     * @return string
     */
    private function getMode(): string
    {
        return $this->scopeConfig->getValue(self::MODE_PATH_XML, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    private function getUrl(): string
    {
        if ($this->getMode() === WhatsAppMode::PROD) {
            return $this->scopeConfig->getValue(self::PROD_URL_PATH_XML, ScopeInterface::SCOPE_STORE);
        }

        return $this->scopeConfig->getValue(self::TEST_URL_PATH_XML, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    private function getApiKey(): string
    {
        if ($this->getMode() === WhatsAppMode::PROD) {
            return $this->encryptor->decrypt($this->scopeConfig->getValue(self::PROD_API_KEY_PATH_XML, ScopeInterface::SCOPE_STORE));
        }

        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::TEST_API_KEY_PATH_XML, ScopeInterface::SCOPE_STORE));
    }
}
