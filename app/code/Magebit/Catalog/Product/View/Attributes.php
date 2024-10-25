<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Product\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    /**
     * $excludeAttr is optional array of attribute codes to exclude them from additional data array
     *
     * @param array $excludeAttr
     * @return array
     * @throws LocalizedException
     */
    public function getAdditionalData(array $excludeAttr = []): array
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->isVisibleOnFrontend($attribute, $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $data[$attribute->getAttributeCode()] = [
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];

                    // Add group information
                    if ($attribute->getAttributeSetId() && $attribute->getAttributeGroupId()) {
                        if (isset($attribute->getAttributeSetInfo()[$attribute->getAttributeSetId()])) {
                            $setInfo = $attribute->getAttributeSetInfo()[$attribute->getAttributeSetId()];

                            $data[$attribute->getAttributeCode()]['set_id'] = $attribute->getAttributeSetId();
                            $data[$attribute->getAttributeCode()]['set_sort'] = $setInfo['sort'];
                            $data[$attribute->getAttributeCode()]['group_id'] = $setInfo['group_id'];
                            $data[$attribute->getAttributeCode()]['group_sort'] = $setInfo['group_sort'];
                        }
                    }
                }
            }
        }
        return $data;
    }
}
