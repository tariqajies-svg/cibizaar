<?php
/**
 * This file is part of the Magebit_HyvaAheadworksQuickOrder package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksQuickOrder
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
namespace Magebit\HyvaAheadworksQuickOrder\Ui\Component\Listing\Item\Column;

use Aheadworks\QuickOrder\Ui\Component\Listing\Item\Column\Qty as QtyDefault;
use Magento\Framework\Exception\NoSuchEntityException;

class Qty
{
    /**
     * Parsing qty
     *
     * @param QtyDefault $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareDataSource(QtyDefault $subject, array $result): array
    {
        if (isset($result['data']['items'])) {
            foreach ($result['data']['items'] as & $item) {
                try {
                    $item['product_qty'] = (float)$item['product_qty'];
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        return $result;
    }
}
