<?php
/**
 * This file is part of the Magebit_HyvaSmileElasticsuite package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaSmileElasticsuite
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaSmileElasticsuite\Block\SmileElasticsuiteSwatches\Navigation\Renderer\Swatches;

use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Eav\Model\Entity\Attribute\Option;
use Hyva\SmileElasticsuite\Block\SmileElasticsuiteSwatches\Navigation\Renderer\Swatches\RenderLayered as BaseRenderLayered;

class RenderLayered extends BaseRenderLayered
{
    /**
     * Get view data for option
     *
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     *
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $dataView = parent::getOptionViewData($filterItem, $swatchOption);

        if ($filterItem->getIsSelected()) {
            $dataView['custom_style'] = ' border-blue';
        }

        return $dataView;
    }
}
