<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Sales\ViewModel;

use Magebit\Sales\Service\ReservedOrderManagement;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ReservedOrders implements ArgumentInterface
{
    /**
     * @param ReservedOrderManagement $reservedOrderManagement
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private readonly ReservedOrderManagement $reservedOrderManagement,
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get amount of products that are reserved
     *
     * @param ProductInterface $product
     * @return float
     */
    public function getReservedProductCount(ProductInterface $product): float
    {
        return $this->reservedOrderManagement->getProductCount($product);
    }

    /**
     * Get amount of product that are reserved by product ID
     *
     * @param int $productId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReservedProductCountById(int $productId): float
    {
        return $this->getReservedProductCount($this->productRepository->getById($productId));
    }

    /**
     * Get products reserved qty
     * Returns all child product qty aswell
     *
     * @param int $productId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductReservedQty(int $productId): array
    {
        $data = [];
        $parentProduct = $this->productRepository->getById($productId);
        if ($parentProduct->getTypeId() === Configurable::TYPE_CODE) {
            $childProducts = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);

            foreach ($childProducts as $child) {
                $data[$child->getId()] = $this->getReservedProductCount($child);
            }
        } else {
            $data[$productId] = $this->getReservedProductCount($parentProduct);
        }

        return $data;
    }
}
