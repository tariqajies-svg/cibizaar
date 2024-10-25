<?php
/**
 * This file is part of the Magebit_LowStock package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_LowStock
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\LowStock\Model\ResourceModel\LowStockNotificationModel;

use Magebit\LowStock\Model\LowStockNotificationModel;
use Magebit\LowStock\Model\ResourceModel\LowStockNotificationResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class LowStockNotificationCollection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magebit_low_stock_notification_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(LowStockNotificationModel::class, LowStockNotificationResource::class);
    }
}
