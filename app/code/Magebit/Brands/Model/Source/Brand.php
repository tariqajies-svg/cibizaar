<?php
/**
 * This file is part of the Magebit_Brands package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Brands
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Brands\Model\Source;

use Magebit\Brands\Api\BrandsManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\NoSuchEntityException;

class Brand extends AbstractSource
{
    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        private readonly AttributeRepository $attributeRepository
    ) {
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllOptions()
    {
        $attribute = $this->attributeRepository->get(
            Product::ENTITY,
            BrandsManagementInterface::PRODUCT_ATTRIBUTE_CODE
        );

        if ($attribute) {
            return $attribute->getSource()->getAllOptions();
        }

        return [];
    }
}
