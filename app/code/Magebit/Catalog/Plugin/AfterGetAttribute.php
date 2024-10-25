<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Plugin;

use Magento\Framework\Phrase;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AfterGetAttribute
{
    /**
     * @param StoreManagerInterface $storeManagerI
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Add weight unit from config
     *
     * @param Output $subject
     * @param string $result
     * @param Product $product
     * @param string|Phrase $attributeHtml
     * @param string $attributeName
     * @return string
     */
    public function afterProductAttribute(Output $subject, string $result, Product $product, $attributeHtml, string $attributeName): string
    {
        if ($attributeName === 'weight') {
            $storeId = $this->storeManager->getStore()->getId();
            $weightUnit =  $this->scopeConfig->getValue(
                'general/locale/weight_unit',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            return round((float) $product->getWeight(), 2) . ' ' . $weightUnit;
        }
        return $result;
    }
}
