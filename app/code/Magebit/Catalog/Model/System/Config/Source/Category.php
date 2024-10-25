<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Catalog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\Catalog\Model\System\Config\Source;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class Category implements OptionSourceInterface
{
    private array $options = [];
    public function __construct(private readonly CategoryManagementInterface $categoryManagement)
    {
    }

    /**
     * Get collection of categories as option array
     *
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $categoryTree =  $this->categoryManagement->getTree();
        $this->setCategoryOptions($categoryTree);

        return $this->options;
    }

    /**
     * @param $category
     * @param int $depth
     */
    private function setCategoryOptions($category, int $depth = 0): void
    {
        foreach ($category->getChildrenData() as $child) {
            $this->options[] = [
                'label' => str_repeat('│ ', $depth) . '├─ ' . $child->getName(),
                'value' => $child->getEntityId()
            ];

            if ($child->getChildrenData()) {
                $this->setCategoryOptions($child, $depth + 1);
            }
        }
    }
}
