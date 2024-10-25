<?php
/**
 * This file is part of the Flurrybox PatchAssist package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Flurrybox PatchAssist
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Flurrybox, Ltd. (https://flurrybox.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Flurrybox\PatchAssist\Model\Service;

use Flurrybox\PatchAssist\Model\ServiceInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Service for attribute actions
 *
 * @api
 */
class Attribute implements ServiceInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * Attribute constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Create attribute for specific entity type
     *
     * Will remove already existing attribute before creating new one
     *
     * @param string $type
     * @param array $data
     *
     * @return Attribute
     * @throws LocalizedException
     */
    public function createAttribute(string $type, array $data): Attribute
    {
        if (!isset($data['attribute_code'])) {
            throw new LocalizedException(new Phrase('Attribute code is required'));
        }

        $entity = $this->getEntitySetupModel($type);
        $entity->removeAttribute($type, $data['attribute_code']);
        $entity->addAttribute($type, $data['attribute_code'], $data);

        return $this;
    }

    /**
     * Update attribute data for specific entity type
     *
     * @param string $type
     * @param string $code
     * @param array $data
     *
     * @return Attribute
     * @throws LocalizedException
     */
    public function updateAttribute(string $type, string $code, array $data): Attribute
    {
        $entity = $this->getEntitySetupModel($type);
        $entity->updateAttribute($type, $code, $data);

        return $this;
    }

    /**
     * Remove attribute for specific entity type
     *
     * @param string $type
     * @param string $code
     *
     * @return Attribute
     * @throws LocalizedException
     */
    public function removeAttribute(string $type, string $code): Attribute
    {
        $entity = $this->getEntitySetupModel($type);
        $entity->removeAttribute($type, $code);

        return $this;
    }

    /**
     * Get entity model
     *
     * @param string $type
     *
     * @return \Magento\Eav\Setup\EavSetup
     * @throws LocalizedException
     */
    protected function getEntitySetupModel(string $type): \Magento\Eav\Setup\EavSetup
    {
        switch ($type) {
            case \Magento\Catalog\Model\Product::ENTITY:
                return $this->eavSetupFactory->create();

            case \Magento\Catalog\Model\Category::ENTITY:
                return $this->categorySetupFactory->create();

            case \Magento\Customer\Model\Customer::ENTITY:
                return $this->customerSetupFactory->create();
        }

        throw new LocalizedException(new Phrase('Incorrect entity type provided'));
    }
}
