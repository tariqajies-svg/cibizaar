<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Model\Backend;

use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

class ProductAttributes extends AbstractBackend
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(protected readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * Before Attribute Save Process
     *
     * @param DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'product_list_attributes') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data) ?: null);
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }
        return $this;
    }

    /**
     * After Load Attribute Process
     *
     * @param DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'product_list_attributes') {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }

            }
        }
        return $this;
    }
}
