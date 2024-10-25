<?php
namespace Ktpl\Catalog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;


class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.1", "<")) {
              $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
         

         $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'brand_product_details',
            [
                'type' => 'text',
                'backend' => '',
                'frontend' => '',
                'label' => 'Brand Products Details',
                'input' => 'pagebuilder',
                'class' => '',
                'source' => '',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'is_wysiwyg_enabled'      => TRUE,
                'unique' => false,
                'apply_to' => ''
            ]
        );

    }
}
}