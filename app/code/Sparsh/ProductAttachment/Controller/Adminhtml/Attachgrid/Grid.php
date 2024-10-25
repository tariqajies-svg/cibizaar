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
namespace Sparsh\ProductAttachment\Controller\Adminhtml\Attachgrid;

/**
 * Class Grid
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Grid extends \Sparsh\ProductAttachment\Controller\Adminhtml\ProductAttachment
{
    /**
     * Rowfactory
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * Layoutfactory
     *
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * CoreRegistry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Grid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader
     * @param \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader,
        \Magento\Framework\Controller\Result\Json $resultJson
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context, $coreRegistry, $attachmentLoader, $resultJson);
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $productattachment = $this->initAttachment();
        if (!$productattachment) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Sparsh\ProductAttachment\Block\Adminhtml\ProductAttachment\Tab\Product::class,
                'attachment.product.grid'
            )->toHtml()
        );
    }
}
