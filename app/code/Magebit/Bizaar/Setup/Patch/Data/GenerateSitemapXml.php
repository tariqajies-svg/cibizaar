<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Bizaar\Setup\Patch\Data;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Magento\Sitemap\Model\SitemapFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class GenerateSitemapXml implements DataPatchInterface
{
    /**
     * Sitemap filename
     *
     * @var string
     */
    private const SITEMAP_FILENAME = 'sitemap.xml';

    /**
     * Sitemap file path
     *
     * @var string
     */
    private const SITEMAP_PATH = '/media/sitemap/';

    /**
     * Default store view code
     *
     * @var string
     */
    private const DEFAULT_STORE_CODE = 'default';

    /**
     * @param State $state
     * @param Filesystem $filesystem
     * @param SitemapFactory $sitemapFactory
     * @param SitemapResource $sitemapResource
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        private readonly State $state,
        private readonly Filesystem $filesystem,
        private readonly SitemapFactory $sitemapFactory,
        private readonly SitemapResource $sitemapResource,
        private readonly StoreRepositoryInterface $storeRepository
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
     * @return void
     */
    public function apply(): void
    {
        try {
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }

        $store = $this->storeRepository->get(self::DEFAULT_STORE_CODE);

        $sitemap = $this->sitemapFactory->create();
        $sitemap->setStoreId($store->getId());
        $sitemap->setSitemapFilename(self::SITEMAP_FILENAME);
        $sitemap->setSitemapPath(self::SITEMAP_PATH);

        $this->sitemapResource->save($sitemap);

        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);

        if ($sitemap->getId()) {
            $fileName = $sitemap->getSitemapFilename();
            $path = $sitemap->getSitemapPath() . '/' . $fileName;

            if ($fileName && $directory->isFile($path)) {
                $directory->delete($path);
            }

            $sitemap->generateXml();
        }
    }
}
