<?php
/**
 * Sparsh ProductAttachment
 * PHP version 8.2
 * 
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\ProductAttachment\ViewModel\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class View
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class View implements ArgumentInterface
{
    /**
     * Attachmentcollection
     *
     * @var \Sparsh\ProductAttachment\Model\ResourceModel\ProductAttachment\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * Current Product
     *
     * @var array currentProduct
     */
    protected $coreRegistry;

    /**
     * Attachments for the product
     *
     * @var array attachments
     */
    protected $items;

    /**
     * Attachments for the product
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Attachments for the product
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * View constructor
     *
     * @param \Sparsh\ProductAttachment\Model\ResourceModel\ProductAttachment\CollectionFactory $itemCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Sparsh\ProductAttachment\Model\ResourceModel\ProductAttachment\CollectionFactory $itemCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Return attachments for the Product
     *
     * @return object Products
     */
    public function getAttachments()
    {
        if (!$this->items) {
            $product = $this->coreRegistry->registry('current_product');

            $this->items = $this->itemCollectionFactory->create()->addFieldToSelect(
                ['title','attach_file']
            )->addFieldToFilter(
                'is_active',
                ['eq' => \Sparsh\ProductAttachment\Model\ProductAttachment::STATUS_ENABLED]
            );
            $this->items->getSelect()->joinLeft(
                ['attatch_products' => $this->items->getTable('sparsh_product_attachment')],
                'main_table.attachment_id = attatch_products.attachment_id',
                ['attatch_products.product_id']
            );
            $this->items->addFieldToFilter(
                'product_id',
                ['eq' => $product->getId()]
            )->addFieldToFilter(
                'title',
                ['notnull' => true]
            )->addFieldToFilter(
                'attach_file',
                ['notnull' => true]
            )
                ->setOrder(
                    'sort_order',
                    'asc'
                );
            ;
        }
        return $this->items;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array identities
     */
    public function getIdentities()
    {
        $product = $this->coreRegistry->registry('current_product');
        return [\Sparsh\ProductAttachment\Model\ProductAttachment::CACHE_TAG . '_' . $product->getId()];
    }

    /**
     * Return identifiers for produced content
     *
     * @return string mediapath
     */
    public function getAttachmentMediaPath()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    /**
     * Return identifiers for produced content
     *
     * @return string mediapath
     */
    public function getConfigValue($config)
    {
        return $this->scopeConfig->getValue($config);
    }

}
