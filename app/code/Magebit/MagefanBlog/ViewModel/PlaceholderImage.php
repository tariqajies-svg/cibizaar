<?php
/**
 * This file is part of the Magebit_MagefanBlog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_MagefanBlog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\MagefanBlog\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class PlaceholderImage implements ArgumentInterface
{
    public const PLACEHOLDER_IMAGE_CONFIG = 'mfblog/placeholder/placeholder_upload';

    public const MAGEFAN_BLOG_PATH = 'magefan_blog/';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param File $fileDriver
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly File $fileDriver
    ) {
    }

    /**
     * Check if file exists in media folder
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function checkMediaFile(string $path): bool
    {
        return $this->fileDriver->isExists(UrlInterface::URL_TYPE_MEDIA . '/' . $path);
    }

    /**
     * Retrieve image path
     *
     * @param string $type
     * @param string $path
     * @return string|null
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function getImage(): ?string
    {
        $path = $this->getImageSrc();

        if ($path) {
            return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     * @throws FileSystemException
     */
    public function getImageSrc(): ?string
    {
        $config = $this->scopeConfig->getValue(self::PLACEHOLDER_IMAGE_CONFIG);
        $path = self::MAGEFAN_BLOG_PATH . $config;

        return $config && $this->checkMediaFile($path)
            ? $path
            : null;
    }
}
