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
 * Class Edit
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * Result Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Page Factory
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Attachment Model
     *
     * @param array
     */
    protected $model;

    /**
     * Edit Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Sparsh\ProductAttachment\Model\ProductAttachment $model
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Sparsh\ProductAttachment\Model\ProductAttachment $model
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->model = $model;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $resultPage = $this->resultPageFactory->create();
        $id = $this->getRequest()->getParam('id');
        $this->model->load($id);
        $this->_coreRegistry->register('currentAttachment', $this->model);
        
        if ($this->getRequest()->getQuery('isAjax')) {
            return $this->ajaxRequestResponse($this->model, $resultPage);
        }
        
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__($this->model->getTitle()));
        return $resultPage;
       // die('hlssoo');
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
         $resultPage->setActiveMenu('Sparsh_ProductAttachment::productattachment')
             ->addBreadcrumb(__('ProductAttachment'), __('Manage Product Attachment'))
             ->addBreadcrumb(__('ProductAttachment'), __('Manage Product Attachment'));

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
