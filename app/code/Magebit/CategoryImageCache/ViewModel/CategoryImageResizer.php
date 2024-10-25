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

namespace Magebit\CategoryImageCache\ViewModel;

use Magebit\CategoryImageCache\Model\Image;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryImageResizer extends DataObject implements ArgumentInterface
{
    private Image $image;

    public function __construct(
        Image $image,
        array $data = []
    ) {
        parent::__construct($data);

        $this->image = $image;
    }

    /**
     * @throws NoSuchEntityException
     * @throws FileSystemException
     */
    public function resize(CategoryInterface $category, ?int $width = null, ?int $height = null): ?string
    {
        return $this->image->resize($category, $width, $height);
    }
}
