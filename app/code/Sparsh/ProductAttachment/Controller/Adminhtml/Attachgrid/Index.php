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
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends \Sparsh\ProductAttachment\Controller\Adminhtml\ProductAttachment
{
    /**
     * ResultPageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader
     * @param \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader,
        \Magento\Framework\Controller\Result\Json $resultJson
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry, $attachmentLoader, $resultJson);
    }

    /**
     * Index action execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Product Attachment'));
        $resultPage->setActiveMenu('Sparsh_ProductAttachment::productattachment');
        return $resultPage;
    }
    
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_ProductAttachment::productattachment');
    }

}
