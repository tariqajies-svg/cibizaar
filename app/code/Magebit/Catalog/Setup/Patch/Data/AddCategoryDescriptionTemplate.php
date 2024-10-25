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

declare(strict_types=1);

namespace Magebit\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\PageBuilder\Model\TemplateFactory;
use Magento\PageBuilder\Model\TemplateRepository;
use Flurrybox\PatchAssist\Model\Service\Template;
use Flurrybox\PatchAssist\Model\Service\Media;
use Magento\Framework\Exception\FileSystemException;

class AddCategoryDescriptionTemplate implements DataPatchInterface
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
     * @var Media
     */
    private Media $media;
    /**
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param Template $template
     * @param Media $media
     */
    public function __construct(
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        Template $template,
        Media $media
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateRepository = $templateRepository;
        $this->template = $template;
        $this->media = $media;
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
     * Add Category Description page builder template
     *
     * @return void
     */
    public function apply(): void
    {
        $this->setMedia();
        $template = $this->templateFactory->create();

        $template->setName('Category Description');
        $template->setTemplate(
            $this->template->get('Magebit_Catalog', 'category-description-template')
        );
        $template->setCreatedFor('category');

        $this->templateRepository->save($template);
    }

    /**
     * @throws FileSystemException
     * @return void
     */
    private function setMedia(): void
    {
        $media = [
            'Magebit_Catalog::image_37.png' => 'wysiwyg/image_37.png',
            'Magebit_Catalog::logo_hp.png' => 'wysiwyg/logo_hp.png'
        ];
        $this->media->copyModuleMedia($media);
    }
}
