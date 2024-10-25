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
namespace Sparsh\ProductAttachment\Controller\Adminhtml;

/**
 * Class ProductAttachment
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
abstract class ProductAttachment extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    protected $attachmentLoader;

    protected $resultJson;

    /**
     * ProductAttachment constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader
     * @param \Magento\Framework\Controller\Result\Json $resultJson
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Sparsh\ProductAttachment\Model\ProductAttachment $attachmentLoader,
        \Magento\Framework\Controller\Result\Json $resultJson
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->attachmentLoader = $attachmentLoader;
        $this->resultJson = $resultJson;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage Resultpage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Sparsh_ProductAttachment::sparsh_productattachment')
            ->addBreadcrumb(__('ProductAttachment'), __('ProductAttachment'))
            ->addBreadcrumb(__('Items'), __(''));
        return $resultPage;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Sparsh_ProductAttachment::sparsh_productattachment');
    }

    /**
     * InitAttachment
     *
     * @return mixed Attachnebtobject
     */
    protected function initAttachment()
    {
        $attachmentId = (int)$this->getRequest()->getParam('id');
        $attachmentId = $attachmentId ? $attachmentId : (int)$this->getRequest()->getParam('attachment_id');

        $productattachment = $this->attachmentLoader;

        if ($attachmentId) {
            $productattachment->load($attachmentId);
        }

        $this->coreRegistry->register('currentAttachment', $productattachment);

        return $productattachment;
    }

    /**
     * Reset Filter Ajax response
     *
     * @param mixed $productattachment Attachment object
     * @param mixed $resultPage        Resultpage
     *
     * @return mixed
     */
    protected function ajaxRequestResponse($productattachment, $resultPage)
    {
        $eventResponse = new \Magento\Framework\DataObject(
            [
            'content' => $resultPage->getLayout()->getUiComponent('productattachment_attachgrid_editing')
                ->getFormHtml(),
            'messages' => $resultPage->getLayout()->getMessagesBlock()->getGroupedHtml(),
            'toolbar' => $resultPage->getLayout()->getBlock('page.actions.toolbar')->toHtml()
            ]
        );

        $this->_eventManager->dispatch(
            'attachment_prepare_ajax_response',
            ['response' => $eventResponse, 'controller' => $this]
        );

        $resultJson = $this->resultJson;
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($eventResponse->getData());
        return $resultJson;
    }
}
