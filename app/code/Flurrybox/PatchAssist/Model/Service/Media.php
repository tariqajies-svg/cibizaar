<?php
/**
 * This file is part of the Flurrybox PatchAssist package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Flurrybox PatchAssist
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Flurrybox, Ltd. (https://flurrybox.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Flurrybox\PatchAssist\Model\Service;

use Flurrybox\PatchAssist\Model\ServiceInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ThemeFactory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Service for media actions
 *
 * @api
 */
class Media implements ServiceInterface
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ThemeFactory
     */
    protected $themeFactory;

    /**
     * @var Theme
     */
    protected $themeResource;

    /**
     * Media constructor.
     *
     * @param DirectoryList $directoryList
     * @param Reader $reader
     * @param Filesystem $filesystem
     * @param ThemeFactory $themeFactory
     * @param Theme $themeResource
     */
    public function __construct(
        DirectoryList $directoryList,
        Reader $reader,
        Filesystem $filesystem,
        ThemeFactory $themeFactory,
        Theme $themeResource
    ) {
        $this->directoryList = $directoryList;
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->themeFactory = $themeFactory;
        $this->themeResource = $themeResource;
    }

    /**
     * Copy media files form design package to media folder
     *
     * @param array $paths
     *
     * @return Media
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copyThemeMedia(array $paths): Media
    {
        foreach ($paths as $from => $to) {
            $this->processCopy($this->getRealFilePathFromTheme($from), $to);
        }

        return $this;
    }

    /**
     * Copy media files from module setup to media folder
     *
     * @param array $paths
     *
     * @return Media
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function copyModuleMedia(array $paths): Media
    {
        foreach ($paths as $from => $to) {
            $this->processCopy($this->getRealFilePathFromModule($from), $to);
        }

        return $this;
    }

    /**
     * Delete media files
     *
     * @param array $paths
     *
     * @return Media
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteMedia(array $paths): Media
    {
        foreach ($paths as $path) {
            $path = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . $path;

            if (!$this->filesystem->exists($path)) {
                continue;
            }

            $this->filesystem->remove($path);
        }

        return $this;
    }

    /**
     * Process file copy
     *
     * @param string $from
     * @param string $to
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function processCopy(string $from, string $to): void
    {
        $to = $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . $to;

        if (!$this->filesystem->exists($from)) {
            return;
        }

        $this->filesystem->copy($from, $to);
    }

    /**
     * Resolve given path to design package
     *
     * @param string $path
     *
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getRealFilePathFromTheme(string $path): string
    {
        [$code, $file] = explode('::', $path, 2);
        $code  = str_replace('_', DIRECTORY_SEPARATOR, $code);

        $theme = $this->themeFactory->create();
        $this->themeResource->load($theme, $code, 'code');

        return implode(DIRECTORY_SEPARATOR, [
            $this->directoryList->getPath(DirectoryList::APP),
            'design',
            $theme->getFullPath(),
            'media',
            $file
        ]);
    }

    /**
     * Resolve given path to module
     *
     * @param string $path
     *
     * @return string
     */
    protected function getRealFilePathFromModule(string $path): string
    {
        [$module, $file] = explode('::', $path, 2);

        return implode(DIRECTORY_SEPARATOR, [
            $this->reader->getModuleDir(Dir::MODULE_SETUP_DIR, $module),
            'Patch',
            'media',
            $file
        ]);
    }
}
