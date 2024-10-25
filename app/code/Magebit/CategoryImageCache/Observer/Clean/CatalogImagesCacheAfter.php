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

namespace Magebit\CategoryImageCache\Observer\Clean;

use Magebit\CategoryImageCache\Model\Image;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\FileSystemException;

/**
 * Clear category images cache from admin panel
 */
class CatalogImagesCacheAfter implements ObserverInterface
{
    protected Image $image;

    public function __construct(
        Image $image
    ) {
        $this->image = $image;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     *
     * @return void
     * @throws FileSystemException
     */
    public function execute(
        Observer $observer
    ) {
        $this->image->clearCache();
    }
}
