<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Catalog\Setup\Patch\Data;

use Flurrybox\PatchAssist\Model\Service\Attribute;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddCategoryFooterToggle implements DataPatchInterface
{
    /**
     * @param Attribute $attribute
     */
    public function __construct(
        private readonly Attribute $attribute
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Add Include In Footer toggle to Category
     *
     * @return void
     */
    public function apply(): void
    {
        $this->attribute->createAttribute(Category::ENTITY, [
            'attribute_code' =>'include_in_footer',
            'type' => 'int',
            'label' => 'Include in Footer',
            'input' => 'select',
            'sort_order' => 25,
            'source' => Boolean::class,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'default' => false,
            'group' => 'general',
            'backend' => ''
        ]);
    }
}
