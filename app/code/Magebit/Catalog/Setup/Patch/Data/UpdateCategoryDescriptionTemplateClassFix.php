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

namespace Magebit\Catalog\Setup\Patch\Data;

use Flurrybox\PatchAssist\Model\Service\Template;
use Magento\PageBuilder\Api\Data\TemplateInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

class UpdateCategoryDescriptionTemplateClassFix implements DataPatchInterface
{
    private const TEMPLATE_NAME = 'Category Description';

    public function __construct(
        private readonly Template $template,
        private readonly TemplateCollectionFactory $templateCollectionFactory
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            AddCategoryDescriptionTemplate::class,
            UpdateCategoryDescriptionTemplate::class
        ];
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
     * Update Category Description page builder template
     *
     * @return void
     */
    public function apply(): void
    {
        $template = $this->templateCollectionFactory->create()
            ->addFieldToFilter(TemplateInterface::KEY_NAME, self::TEMPLATE_NAME)
            ->getFirstItem();
        if ($template->getId()) {
            $template->setTemplate(
                $this->template->get('Magebit_Catalog', 'category-description-template')
            );
            $template->save();
        }
    }
}
