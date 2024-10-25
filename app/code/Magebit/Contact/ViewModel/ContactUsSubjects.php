<?php
/**
 * This file is part of the Magebit_Contact package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Contact
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Contact\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ContactUsSubjects implements ArgumentInterface
{
    private const CONFIG_PATH = 'contact_us/subject/options';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Get available Subject options for contact us page
     *
     * @return array
     */
    public function getOptions(): array
    {
        $config = $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORES);

        if (!$config) {
            return [];
        }

        return array_column($this->serializer->unserialize($config), 'name');
    }
}
