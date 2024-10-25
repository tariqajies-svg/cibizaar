<?php
/**
 * This file is part of the Magebit_ImageResizer package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_ImageResizer
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\ImageResizer\ViewModel;

use Magebit\ImageResizer\Service\ResizeService;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class ImageResizer implements ArgumentInterface
{
    /**
     * @param ResizeService $resizeService
     * @param Filesystem $filesystem
     * @param File $fileDriver
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResizeService $resizeService,
        private readonly Filesystem $filesystem,
        private readonly File $fileDriver,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Resize image to certain height/width
     *
     * @param string $path
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function resize(string $path, ?int $width = null, ?int $height = null, bool $crop = false): string
    {
        return $this->resizeService->resize($path, $width, $height, $crop);
    }

    /**
     * @return string
     */
    public function getMediaDirectoryAbsolutePath(): string
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * @param string|null $path
     * @return bool
     */
    public function fileExists(?string $path): bool
    {
        try {
            return $path && $this->fileDriver->isExists($path) && $this->fileDriver->isFile($path);
        } catch (FileSystemException $e) {
            $this->logger->error(__('ImageResizer: error checking for file existence: %1', $e->getMessage()));
            return false;
        }
    }
}
