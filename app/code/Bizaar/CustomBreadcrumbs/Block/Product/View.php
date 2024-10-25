<?php
namespace Bizaar\CustomBreadcrumbs\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

class View extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * View constructor.
     * 
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current product.
     *
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}
