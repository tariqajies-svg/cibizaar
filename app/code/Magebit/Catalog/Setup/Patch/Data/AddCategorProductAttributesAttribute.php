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

namespace Magebit\Catalog\Setup\Patch\Data;

use Magebit\Catalog\Model\Backend\ProductAttributes;
use Magebit\Catalog\Model\Source\ProductAttributes as ProductAttributesSource;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCategorProductAttributesAttribute implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * Creates brand category attribute
     *
     * @return void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Category::ENTITY,
            'product_list_attributes',
            [
                'type' => 'text',
                'label' => 'Product List Attributes',
                'input' => 'multiselect',
                'source' => ProductAttributesSource::class,
                'backend' => ProductAttributes::class,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'sort_order' => 20,
                'required' => false,
                'group' => 'display_settings',
            ]
        );
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
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
}
