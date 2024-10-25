<?php
/**
 * This file is part of the Magebit_Brands package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Brands
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Brands\Plugin\File\Validator;

use Magento\Framework\App\RequestInterface;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use MagestyApps\WebImages\Helper\ImageHelper;

/**
 * Plugin which allows to upload vector extension images in file uploader
 */
class NotProtectedExtensionPlugin
{
    /**
     * @param ImageHelper      $imageHelper
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly ImageHelper $imageHelper,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Remove vector images from protected extensions list
     *
     * @param NotProtectedExtension $subject
     * @param array $result
     * @return array
     */
    public function afterGetProtectedFileExtensions(NotProtectedExtension $subject, array $result): array
    {
        if (str_contains($this->request->getPathInfo(), 'catalog/brand_logo/upload')) {
            return $result;
        }

        $vectorExtensions = $this->imageHelper->getVectorExtensions();

        if (is_string($result)) {
            $result = explode(',', $result);
        }

        foreach (array_keys($result) as $extension) {
            if (in_array($extension, $vectorExtensions)) {
                unset($result[$extension]);
            }
        }

        return $result;
    }
}
