<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Bizaar\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zend_Validate_Exception;

/**
 * Add Short name attribute on product page
 */
class RemoveBrandAttribute implements DataPatchInterface
{
    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        protected readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply(): void
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(Product::ENTITY, 'brand');
    }
}
