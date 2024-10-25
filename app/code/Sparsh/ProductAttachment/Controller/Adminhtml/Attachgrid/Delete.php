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

use Magento\Backend\App\Action;

/**
 * Class Delete
 *
 * @category Sparsh
 * @package  Sparsh_ProductAttachment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.comm
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Admin Resource
     *
     * @param string
     */
    const ADMIN_RESOURCE = 'Sparsh_ProductAttachment::productattachment';

    /**
     * Attachment Model
     *
     * @param array
     */
    protected $model;

    /**
     * Delete constructor
     *
     * @param Action\Context $context
     * @param \Sparsh\ProductAttachment\Model\ProductAttachment $model
     */
    public function __construct(
        Action\Context $context,
        \Sparsh\ProductAttachment\Model\ProductAttachment $model
    ) {
        $this->model = $model;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return $this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                $this->model->load($id);
                $title = $this->model->getTitle();
                $this->model->delete();
                $this->messageManager->addSuccess(__('The attachment has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a attachment to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
