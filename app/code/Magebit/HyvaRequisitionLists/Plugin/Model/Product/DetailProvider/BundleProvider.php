<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);
namespace Magebit\HyvaRequisitionLists\Plugin\Model\Product\DetailProvider;

use Aheadworks\RequisitionLists\Model\Product\DetailProvider\BundleProvider as BundleProviderOrigin;

class BundleProvider extends BundleProviderOrigin
{
    /**
     * @return string
     *
     */
    public function getProductImageUrl(): string
    {
        $productImageBlock = $this->productImageFactory->create(
            $this->product,
            'product_page_image_small',
            []
        );

        return $productImageBlock->getImageUrl();
    }
}
