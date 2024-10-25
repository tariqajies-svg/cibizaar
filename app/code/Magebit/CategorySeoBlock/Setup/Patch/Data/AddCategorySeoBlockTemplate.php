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

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\PageBuilder\Model\TemplateRepository;
use Flurrybox\PatchAssist\Model\Service\Template;

class AddCategorySeoBlockTemplate implements DataPatchInterface
{
    /**
     * @var TemplateFactory
     */
    private TemplateFactory $templateFactory;

    /**
     * @var TemplateRepository
     */
    private TemplateRepository $templateRepository;

    /**
     * @var Template
     */
    private Template $template;

    /**
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param Template $template
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        Template $template
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
        $this->template = $template;
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [
            AddCategorySeoBlockAttribute::class
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
     * Add Category SEO Block page builder template
     *
     * @return void
     */
    public function apply(): void
    {
        $template = $this->templateFactory->create();

        $template->setName('Category SEO Block');
        $template->setTemplate(
            $this->template->get('Magebit_CategorySeoBlock', 'seo-block-template')
        );
        $template->setCreatedFor('category');

        $this->templateRepository->save($template);
    }
}
