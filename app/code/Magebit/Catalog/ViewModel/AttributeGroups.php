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

namespace Magebit\Catalog\ViewModel;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AttributeGroups implements ArgumentInterface
{
    public const NO_GROUP_ID = '-1'; // -1 means that attribute has no group
    public const NO_GROUP_SORT = '-1'; // -1 means that attribute has no group, and it should go first in the list

    /**
     * @param CollectionFactory $attributeGroupCollectionFactory
     */
    public function __construct(
        protected readonly CollectionFactory $attributeGroupCollectionFactory
    ) {
    }

    /**
     * This method add group names.
     * Parameter format is an array, each element should have 'group_id' parameter.
     *
     * @param array $attributes
     * @return array
     */
    public function addGroupName(array $attributes): array
    {
        $collection = $this->attributeGroupCollectionFactory->create()
            ->addFieldToSelect(['attribute_group_name', 'attribute_group_id'])
            ->addFieldToFilter('attribute_group_id', ['in' => array_column($attributes, 'group_id')]);

        foreach ($attributes as &$attribute) {
            if (empty($attribute['group_id'])) {
                $attribute['group_id'] = self::NO_GROUP_ID;
                $attribute['group_name'] = '';
            } else {
                $attribute['group_name'] = $collection->getItemById($attribute['group_id'])->getAttributeGroupName();
            }
        }

        return $attributes;
    }

    /**
     * Group attributes by group_id
     *
     * @param array $attributes
     * @return array
     */
    public function groupByGroup(array $attributes): array
    {
        $groupsArray = [];

        // Grouping
        foreach ($attributes as $attributeCode => $attribute) {
            if (empty($attribute['group_id'])) {
                continue;
            }

            if ($attribute['group_id'] === self::NO_GROUP_ID) {
                $groupsArray[$attribute['group_id']]['group_sort'] = self::NO_GROUP_SORT;
            } else {
                $groupsArray[$attribute['group_id']]['group_id'] = $attribute['group_id'];
                $groupsArray[$attribute['group_id']]['group_name'] = $attribute['group_name'];
                $groupsArray[$attribute['group_id']]['group_sort'] = $attribute['group_sort'];
            }

            $groupsArray[$attribute['group_id']]['attributes'][$attributeCode] = $attribute;
        }

        // Sorting
        usort($groupsArray, function ($a, $b) {
            return $a['group_sort'] - $b['group_sort'];
        });

        return $groupsArray;
    }
}
