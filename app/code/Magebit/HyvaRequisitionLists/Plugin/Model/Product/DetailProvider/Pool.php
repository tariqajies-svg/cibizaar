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

use Aheadworks\RequisitionLists\Api\RequisitionListItemRepositoryInterface;
use Aheadworks\RequisitionLists\Model\Product\DetailProvider\Pool as PoolOrigin;
use Aheadworks\RequisitionLists\Model\Product\DetailProvider\SimpleProvider;
use Aheadworks\RequisitionLists\Model\RequisitionList\Item\Options\Converter as OptionConverter;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Pool extends PoolOrigin
{
    /**
     * Provider type
     */
    public const PROVIDER_TYPE_DEFAULT = 'simple';

    // @codingStandardsIgnoreStart
    /**
     * @param Grouped $groupedType
     * @param ProductRepositoryInterface $productRepository
     * @param SimpleProvider $simpleProvider
     * @param RequisitionListItemRepositoryInterface $requisitionListItemRepository
     * @param OptionConverter $optionConverter
     * @param array $providers
     */
    public function __construct(
        Grouped $groupedType,
        ProductRepositoryInterface $productRepository,
        SimpleProvider $simpleProvider,
        RequisitionListItemRepositoryInterface $requisitionListItemRepository,
        OptionConverter $optionConverter,
        array $providers = []
    ) {
        parent::__construct(
            $groupedType,
            $productRepository,
            $simpleProvider,
            $requisitionListItemRepository,
            $optionConverter,
            $providers
        );
    }
    // @codingStandardsIgnoreEnd
}
