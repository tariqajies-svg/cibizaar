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

namespace Magebit\LowStock\Model\ResourceModel;

use Magebit\LowStock\Api\Data\LowStockNotificationInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LowStockNotificationResource extends AbstractDb
{
    /**
     * @var string
     */
    protected string $_eventPrefix = 'magebit_low_stock_notification_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('magebit_low_stock_notification', LowStockNotificationInterface::ID);
        $this->_useIsObjectNew = true;
    }
}
