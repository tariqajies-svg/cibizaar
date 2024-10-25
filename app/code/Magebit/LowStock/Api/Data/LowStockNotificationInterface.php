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

namespace Magebit\LowStock\Api\Data;

interface LowStockNotificationInterface
{
    /**
     * String constants for property names
     */
    public const ID = 'id';
    public const SKU = 'sku';
    public const CREATED_AT = 'created_at';

    /**
     * Getter for Id.
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Getter for Sku.
     *
     * @return string|null
     */
    public function getSku(): ?string;

    /**
     * Setter for Sku.
     *
     * @param string|null $sku
     *
     * @return LowStockNotificationInterface
     */
    public function setSku(?string $sku): self;

    /**
     * Getter for CreatedAt.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Setter for CreatedAt.
     *
     * @param string|null $createdAt
     *
     * @return LowStockNotificationInterface
     */
    public function setCreatedAt(?string $createdAt): self;
}
