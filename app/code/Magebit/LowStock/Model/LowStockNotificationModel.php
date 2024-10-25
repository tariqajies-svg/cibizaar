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

namespace Magebit\LowStock\Model;

use Magebit\LowStock\Api\Data\LowStockNotificationInterface;
use Magebit\LowStock\Model\ResourceModel\LowStockNotificationResource;
use Magento\Framework\Model\AbstractModel;

class LowStockNotificationModel extends AbstractModel implements LowStockNotificationInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magebit_low_stock_notification_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(LowStockNotificationResource::class);
    }

    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getData(self::ID) === null ? null
            : (int)$this->getData(self::ID);
    }

    /**
     * Getter for Sku.
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * Setter for Sku.
     *
     * @param string|null $sku
     *
     * @return LowStockNotificationInterface
     */
    public function setSku(?string $sku): LowStockNotificationInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Getter for CreatedAt.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Setter for CreatedAt.
     *
     * @param string|null $createdAt
     *
     * @return LowStockNotificationInterface
     */
    public function setCreatedAt(?string $createdAt): LowStockNotificationInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
