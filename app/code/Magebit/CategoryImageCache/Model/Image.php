<?php
/**
 * This file is part of the Magebit_CategoryImageCache package.
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

namespace Magebit\CategoryImageCache\Model;

use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Resize functionality for category image.
 * Resized images will be stored in: pub/media/catalog/category/cache
 * Images are saved to cache and regenerates only if cache is empty or image not exists
 */
class Image
{
    public const CATEGORY_DIRECTORY = 'catalog/category';
    public const CACHE_DIRECTORY = self::CATEGORY_DIRECTORY . '/cache';

    protected Filesystem $filesystem;
    protected AdapterFactory $imageFactory;
    protected StoreManagerInterface $storeManager;
    protected WriteInterface $mediaDirectory;
    protected DirectoryList $directoryList;
    protected CacheInterface $cacheManager;
    protected SerializerInterface $serializer;
    protected ScopeConfigInterface $scopeConfig;
    protected FileInfo $fileInfo;
    protected File $fileSystemIo;

    private string $cachePrefix = 'IMG_CATEGORY_INFO';

    /**
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        Context $context,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        File $fileSystemIo,
        FileInfo $fileInfo,
        private readonly LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->cacheManager = $context->getCacheManager();
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->fileSystemIo = $fileSystemIo;
        $this->fileInfo = $fileInfo;
    }

    /**
     * Resize category image
     *
     * @param CategoryInterface $category
     * @param int|null $width - in px
     * @param int|null $height - in px
     *
     * @return string|null - return url to the resized image or null if category don't have image
     *
     * @throws FileSystemException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function resize(CategoryInterface $category, ?int $width = null, ?int $height = null): ?string
    {
        // If category do not have image, use placeholder
        if (!$imagePath = $category->getImage()) {
            return null;
        }

        // Collect file and directory paths
        $imageName = $this->getFileBasename($imagePath);

        if ($imageName) {
            $mediaBaseUrl = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            if ($this->fileInfo->isBeginsWithMediaDirectoryPath($imageName)) {
                $relativePath = $this->fileInfo->getRelativePathToMediaDirectory($imageName);
                $imagePath = rtrim($mediaBaseUrl, '/') . '/' . ltrim($relativePath, '/');
            } elseif (!str_starts_with($imageName, '/')) {
                $newImagePath = rtrim($mediaBaseUrl, '/') . '/' . ltrim(FileInfo::ENTITY_MEDIA_PATH, '/') . '/' . $imageName;

                if (!str_ends_with($newImagePath, $imagePath)) {
                    $imageName = preg_replace('/^.*?' . str_replace('/', '\/', FileInfo::ENTITY_MEDIA_PATH) . '/', '', $imagePath);
                    $newImagePath = rtrim($mediaBaseUrl, '/') . '/' . ltrim(FileInfo::ENTITY_MEDIA_PATH, '/') . $imageName;
                }

                $imagePath = $newImagePath;
            } else {
                $imagePath = $imageName;
            }
        } else {
            throw new LocalizedException(
                __('Something went wrong while getting the image url.')
            );
        }

        $originalAbsolutePath = $imagePath;
        $newRelativeDirectoryPath = $this->getImageDirectory((int)$category->getId(), $width, $height);
        $newRelativeFilePath = $this->mediaDirectory->getAbsolutePath($newRelativeDirectoryPath) . $imageName;

        // Retrieve image from cache instead of generate a new one
        if (is_array($this->loadImageInfoFromCache($newRelativeFilePath)) && $this->fileExists($newRelativeFilePath)) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $newRelativeDirectoryPath . $imageName;
        }

        try {
            // Generate new image
            $this->generateImage($originalAbsolutePath, $newRelativeFilePath, $width, $height);
            $this->saveImageInfoToCache(
                ['category_id' => $category->getId(), 'size' => $width . 'x' . $height],
                $newRelativeFilePath
            );

            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $newRelativeDirectoryPath . $imageName;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), [
                'category' => $category->getName(),
                'id' => $category->getId()
            ]);
            return $category->getImageUrl();
        }
    }

    /**
     * @param int $categoryId
     * @param int|null $width
     * @param int|null $height
     *
     * @return string
     */
    protected function getImageDirectory(int $categoryId, ?int $width = null, ?int $height = null): string
    {
        $path = self::CACHE_DIRECTORY . '/' . $categoryId;

        if ($width !== null || $height !== null) {
            $path .= '/' . $width . 'x' . $height;
        }

        return $path;
    }

    /**
     * Load image data from cache
     *
     * @param string $imagePath
     * @return array|false
     */
    private function loadImageInfoFromCache(string $imagePath): bool|array
    {
        $imagePath = $this->cachePrefix . $imagePath;
        $cacheData = $this->cacheManager->load($imagePath);
        if (!$cacheData) {
            return false;
        } else {
            return $this->serializer->unserialize($cacheData);
        }
    }

    /**
     * First check this file on FS
     *
     * @param string $filename
     * @return bool
     */
    protected function fileExists(string $filename): bool
    {
        if ($this->mediaDirectory->isFile($filename)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $imageOriginalPath
     * @param string $imageResizedPath
     * @param int|null $width
     * @param int|null $height
     *
     * @return void
     *
     * @throws Exception
     */
    protected function generateImage(
        string $imageOriginalPath,
        string $imageResizedPath,
        ?int $width,
        ?int $height
    ): void {
        $imageFactory = $this->imageFactory->create();
        $imageFactory->open($imageOriginalPath);
        $imageFactory->keepAspectRatio(true);
        $imageFactory->resize($width, $height);
        $imageFactory->save($imageResizedPath);
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getFileBasename(string $path): string
    {
        return $this->fileSystemIo->getPathInfo($path)['basename'];
    }

    /**
     * Save image data to cache
     *
     * @param array $imageInfo
     * @param string $imagePath
     *
     * @return void
     */
    private function saveImageInfoToCache(array $imageInfo, string $imagePath): void
    {
        $imagePath = $this->cachePrefix . $imagePath;
        $this->cacheManager->save(
            $this->serializer->serialize($imageInfo),
            $imagePath,
            [$this->cachePrefix]
        );
    }

    /**
     * Clear cache
     *
     * @return void
     * @throws FileSystemException
     */
    public function clearCache(): void
    {
        $directory = self::CACHE_DIRECTORY;
        $directoryToDelete = $directory;

        try {
            //generate name in format: \.[0-9A-ZA-z-_]{3} (e.g .QX3)
            $tmpDirBasename = strrev(strtr(base64_encode(random_bytes(2)), '+/=', '-_.'));
            $tmpDirectory = $this->directoryList->getPath('pub') . '/media/' . self::CATEGORY_DIRECTORY . '/' . $tmpDirBasename;
            //delete temporary directory if exists
            if ($this->mediaDirectory->isDirectory($tmpDirectory)) {
                $this->mediaDirectory->delete($tmpDirectory);
            }
            //rename the directory to simulate deletion and delete the destination directory
            if ($this->mediaDirectory->isDirectory($directory) &&
                true === $this->mediaDirectory->getDriver()->rename(
                    $this->mediaDirectory->getAbsolutePath($directory),
                    $this->mediaDirectory->getAbsolutePath($tmpDirectory)
                )
            ) {
                $directoryToDelete = $tmpDirectory;
            }
        } catch (Throwable $exception) {
            //ignore exceptions thrown during renaming
            $directoryToDelete = $directory;
        }

        $this->mediaDirectory->delete($directoryToDelete);

        $this->clearImageInfoFromCache();
    }

    /**
     * Clear image data from cache
     *
     * @return void
     */
    private function clearImageInfoFromCache(): void
    {
        $this->cacheManager->clean([$this->cachePrefix]);
    }
}
