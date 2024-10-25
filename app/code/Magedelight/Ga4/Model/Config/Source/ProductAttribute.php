<?php

/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@magedelight.com>
 *
 * @category  Magedelight
 * @package   Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.magedelight.com/)
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author    Magedelight <info@magedelight.com>
 */
 
namespace Magedelight\Ga4\Model\Config\Source;

class ProductAttribute implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;
    
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];

        $attributesCollection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('used_in_product_listing', 1);
        foreach ($attributesCollection as $attribute) {
            $arr[] = array('value' => $attribute->getData('attribute_code'),'label' => $attribute->getData('frontend_label'));
        }
        return $arr;
    }
}
