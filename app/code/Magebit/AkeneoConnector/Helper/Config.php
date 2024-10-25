<?php
/**
 * This file is part of the Magebit_AkeneoConnector package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_BankTransfer
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AkeneoConnector\Helper;

use Akeneo\Connector\Helper\Config as ConfigHelper;

class Config extends ConfigHelper
{
    /**
     * Product variant media attributes config path
     */
    public const PRODUCT_VARIANT_MEDIA_GALLERY = 'akeneo_connector/product/media_gallery_variant';

    /**
     * Retrieve product variant media attribute column
     *
     * @return array
     */
    public function getMediaImportVariantColumns(): array
    {
        $images = [];
        /** @var string $config */
        $config = $this->scopeConfig->getValue(self::PRODUCT_VARIANT_MEDIA_GALLERY);
        if (!$config) {
            return $images;
        }

        /** @var array $media */
        $media = $this->jsonSerializer->unserialize($config);
        if (!$media) {
            return $images;
        }

        foreach ($media as $image) {
            if (!isset($image['attribute']) || $image['attribute'] === '') {
                continue;
            }
            $images[] = $image['attribute'];
        }

        return $images;
    }

    /**
     * Retrieve default website id
     *
     * @return int
     */
    public function getDefaultWebsiteId(): int
    {
        return (int)$this->storeManager->getDefaultStoreView()->getWebsiteId();
    }
}
