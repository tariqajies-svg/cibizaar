<?php
/**
 * Magedelight
 *
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Gtag;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var $storedData
     */
    protected $storedData;

    /**
     * @var $resultPageFactory
     */
    public $resultPageFactory;

     /**
      * Construct
      *
      * @param Context $context
      * @param PageFactory $resultPageFactory
      */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magedelight_Ga4::ga4');
        $resultPage->addBreadcrumb(__('Realtime Tracking Logs'), __('Realtime Tracking Logs'));
        $resultPage->getConfig()->getTitle()->prepend(__('Realtime Tracking Logs'));
        return $resultPage;
    }

    /**
     * IsAllowed
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Ga4::ga4');
    }
}
