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

namespace Magebit\ImageResizer\Service;

use Exception;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ResizeService
{
    private const CACHE_DIR = '/cache';
    private const CACHE_PREFIX = 'MAGEBIT_IMG_RESIZE';

    /**
     * @param File $fileSystemIo
     * @param Filesystem $filesystem
     * @param CacheInterface $cacheManager
     * @param SerializerInterface $serializer
     * @param StoreManagerInterface $storeManager
     * @param AdapterFactory $imageFactory
     */
    public function __construct(
        private readonly File $fileSystemIo,
        private readonly Filesystem $filesystem,
        private readonly CacheInterface $cacheManager,
        private readonly SerializerInterface $serializer,
        private readonly StoreManagerInterface $storeManager,
        private readonly AdapterFactory $imageFactory
    ) {
    }

    /**
     * Resize image to defined dimensions
     * Places image under cache folder in the same directory as source file
     * and changes name to <name>_<width>_<height>.<extension>
     * pub/media/folder/image.jpg -> pub/media/folder/cache/image_20_60.jpg
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
        $mediaBaseUrl = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();
        $relativePath = $this->getRelativeDirectoryPath($path);
        $path = rtrim($mediaBaseUrl, '/') . '/' . ltrim($relativePath, '/');

        $originalDirAbsolutePath = $this->fileSystemIo->getPathInfo($path)['dirname'];
        $cacheFile = $originalDirAbsolutePath . self::CACHE_DIR . '/' . $this->getFileName($path, $width, $height);
        $relativeCacheFile = $this->getRelativeDirectoryPath($cacheFile);

        if ($this->loadImageInfoFromCache($cacheFile) && $this->fileExists($cacheFile)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativeCacheFile;
        }

        $this->generateImage($path, $cacheFile, $width, $height, $crop);
        $this->saveImageToCache(['size' => $width . 'x' . $height], $cacheFile);

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $relativeCacheFile;
    }

    /**
     * Generate new image and place it in target directory
     *
     * @param string $originalPath
     * @param string $targetPath
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @return void
     * @throws Exception
     */
    public function generateImage(string $originalPath, string $targetPath, ?int $width = null, ?int $height = null, bool $crop = false): void
    {
        $image = $this->imageFactory->create();

        $image->open($originalPath);

        if (!$crop) {
            $image->keepAspectRatio(true);
            $image->resize($width, $height);
        } else {
            /**
             * If $crop set to "true" - we will resize image based on height and width to match the smallest side,
             * then cut the widest side with anchor in center.
             */
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(false);

            $originalWidth = $image->getOriginalWidth();
            $originalHeight = $image->getOriginalHeight();

            $oldAspectRatio = $originalWidth / $originalHeight;
            $newAspectRatio = $width / $height;

            if ($oldAspectRatio > $newAspectRatio) {
                // original image is wider than the desired dimensions
                $image->resize(null, $height);
                $crop = round(($image->getOriginalWidth() - $width) / 2);
                $image->crop(0, $crop, $crop, 0);
            } else {
                // it's taller...
                $image->resize($width, null);
                $crop = round(($image->getOriginalHeight() - $height) / 2);
                $image->crop($crop, 0, 0, $crop);
            }
        }

        $image->save($targetPath);
    }

    /**
     * Load image from cache
     *
     * @param string $path
     * @return bool|array
     */
    private function loadImageInfoFromCache(string $path): bool|array
    {
        $imagePath = self::CACHE_PREFIX . $path;
        $cacheData = $this->cacheManager->load($imagePath);

        if (!$cacheData) {
            return false;
        } else {
            return $this->serializer->unserialize($cacheData);
        }
    }

    /**
     * @param array $imageInfo
     * @param string $imagePath
     * @return void
     */
    private function saveImageToCache(array $imageInfo, string $imagePath): void
    {
        $cacheTag = self::CACHE_PREFIX . $imagePath;
        $this->cacheManager->save(
            $this->serializer->serialize($imageInfo),
            $cacheTag,
            [self::CACHE_PREFIX]
        );
    }

    /**
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    private function fileExists(string $path): bool
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->isFile($path);
    }

    /**
     * @param string $path
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    private function getFileName(string $path, ?int $width = null, ?int $height = null): string
    {
        $pathInfo = $this->fileSystemIo->getPathInfo($path);

        return sprintf(
            '%s_%s_%s.%s',
            $pathInfo['filename'],
            $width ?? 0,
            $height ?? 0,
            $pathInfo['extension']
        );
    }

    /**
     * Get relative path to media
     *
     * @param string $path
     * @return string
     * @throws FileSystemException
     */
    private function getRelativeDirectoryPath(string $path): string
    {
        $mediaDirPath = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath();
        $baseDirPath = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->getAbsolutePath();
        $pubDirPath = $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->getAbsolutePath();

        $relativePub = substr($pubDirPath, strlen($baseDirPath));
        $relativeMedia = substr($mediaDirPath, strlen($pubDirPath));

        foreach ([$baseDirPath, $relativePub, $relativeMedia] as $dir) {
            if (str_starts_with($path, $dir)) {
                $path = substr($path, strlen($dir));
            }
        }

        return $path;
    }
}
