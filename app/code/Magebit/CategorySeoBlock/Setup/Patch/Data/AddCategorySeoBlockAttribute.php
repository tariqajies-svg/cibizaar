<?php
/**
 * This file is part of the Magebit_CategorySeoBlock package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_CategorySeoBlock
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\CategorySeoBlock\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddCategorySeoBlockAttribute implements DataPatchInterface
{
    /**
     * @var CategorySetupFactory
     */
    private CategorySetupFactory $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
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
     * Add category attribute for SEO content
     *
     * @return void
     */
    public function apply(): void
    {
        $categorySetup = $this->categorySetupFactory->create();

        $categorySetup->addAttribute(
            Category::ENTITY,
            'category_seo_block',
            [
                'type' => 'text',
                'label' => 'SEO Block',
                'input' => 'textarea',
                'backend' => ArrayBackend::class,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'required' => false,
                'sort_order' => 10,
                'global' => ScopedAttributeInterface::SCOPE_STORE
            ]
        );
    }
}
