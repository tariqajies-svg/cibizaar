<?php
/**
 * This file is part of the Magebit_GhoSterOutOfStockAtLast package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\GhoSterOutOfStockAtLast\Plugin\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Data\Collection as CollectionAlias;
use Magento\Framework\DB\Select;

/**
 * Class CollectionPlugin applying sort order
 */
class CollectionPlugin
{
    /**
     * @var array
     */
    private array $skipFlags = [];

    /**
     * Setting order and determine flags
     *
     * @param Collection $subject
     * @param mixed $attribute
     * @param array|string $dir
     * @return array
     */
    public function beforeSetOrder(
        Collection   $subject,
        mixed        $attribute,
        array|string $dir = Select::SQL_DESC
    ): array {
        $subject->setFlag('is_processing', true);
        $this->applyOutOfStockAtLastOrders($subject);

        $flagName = $this->_getFlag($attribute);

        if ($subject->getFlag($flagName)) {
            $this->skipFlags[] = $flagName;
        }

        $subject->setFlag('is_processing', false);
        return [$attribute, $dir];
    }

    /**
     * Get flag by attribute
     *
     * @param string $attribute
     * @return string
     */
    private function _getFlag(string $attribute): string
    {
        return 'sorted_by_' . $attribute;
    }

    /**
     * Try to determine applied sorting attribute flags
     *
     * @param Collection $subject
     * @param callable $proceed
     * @param mixed $attribute
     * @param array|string $dir
     * @return Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetOrder(
        Collection $subject,
        callable   $proceed,
        mixed      $attribute,
        array|string $dir = Select::SQL_DESC
    ): Collection {
        $flagName = $this->_getFlag($attribute);
        if (!in_array($flagName, $this->skipFlags)) {
            $proceed($attribute, $dir);
        }

        return $subject;
    }

    /**
     * Apply sort orders
     *
     * @param Collection $collection
     */
    private function applyOutOfStockAtLastOrders(Collection $collection): void
    {
        if (!$collection->getFlag('sorted_by_oos_flag')) {
            $collection->setFlag('sorted_by_oos_flag', true);
            $collection->setOrder('out_of_stock_at_last', Select::SQL_DESC);
        }
    }

    /**
     * Determine and set order if necessary
     *
     * @param Collection $subject
     * @param mixed $attribute
     * @param array|string $dir
     * @return array
     */
    public function beforeAddOrder(
        Collection   $subject,
        mixed        $attribute,
        array|string $dir = Select::SQL_DESC
    ): array {
        if (!$subject->getFlag('is_processing')) {
            $result = $this->beforeSetOrder($subject, $attribute, $dir);
        }

        return $result ?? [$attribute, $dir];
    }

    /**
     * Prevent double sorting by some attribute.
     *
     * @param Collection $collection
     * @param callable $proceed
     * @param string $attribute
     * @param array|string $dir
     * @return Collection
     */
    public function aroundAddAttributeToSort(
        Collection $collection,
        callable $proceed,
        string $attribute,
        array|string $dir = CollectionAlias::SORT_ORDER_ASC
    ): Collection {
        if (!$collection->getFlag(sprintf('sorted_by_%s_attribute', $attribute))) {
            $collection->setFlag(sprintf('sorted_by_%s_attribute', $attribute), true);
            $proceed($attribute, $dir);
        }

        return $collection;
    }
}
