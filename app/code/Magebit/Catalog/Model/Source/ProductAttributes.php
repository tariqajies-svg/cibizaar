<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\Model\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductAttributes extends AbstractSource
{
    /**
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly AttributeRepository $attributeRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('used_in_product_listing', 1)
            ->addFilter('is_user_defined', 1)
            ->create();

            $attributes = $this->attributeRepository->getList(Product::ENTITY, $searchCriteria);

            if ($attributes->getTotalCount()) {
                foreach ($attributes->getItems() as $attribute) {
                    $this->_options[] = [
                        'value' => $attribute->getAttributeId(),
                        'label' => $attribute->getDefaultFrontendLabel()
                    ];
                }
            }
        }

        return $this->_options;
    }
}
