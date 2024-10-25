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
namespace Magebit\HyvaRequisitionLists\Plugin\Item\Listing\Column;

use Aheadworks\RequisitionLists\Api\Data\RequisitionListItemInterface;

use Magebit\HyvaRequisitionLists\Plugin\Model\Product\DetailProvider\Pool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Exception\NoSuchEntityException;

class Name extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Pool $pool
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly Pool $pool,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $provider = $this->pool->getProvider($item);

                    $item['image_html'] = $provider->getProductImageHtml();
                    $item['image_url'] = $provider->getProductImageUrl();
                    $item['product_name_url'] = $provider->getProductUrl($item['product_qty']);
                    $item['is_available'] = $provider->isAvailableForSite();
                    $item['is_salable'] = $provider->isSalable();
                    $item['is_disabled'] = $provider->isDisabled();
                    $item['is_editable'] = $provider->isEditable();
                    $item['is_qty_enabled'] = $provider->isQtyEnabled();
                    $item['is_out_of_stock'] = !$provider->getQtyIsSalable($item['product_qty']);
                    $item['product_attributes'] =
                        $provider->getProductAttributes($item[RequisitionListItemInterface::PRODUCT_OPTION]);
                } catch (NoSuchEntityException $e) {
                    $item['is_available'] = false;
                    $item['product_attributes'] = [];
                }
            }
        }

        return $dataSource;
    }
}
