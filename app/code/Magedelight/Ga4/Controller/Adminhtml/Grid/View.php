<?php
/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class View extends Action
{
    protected $resultRawFactory;
    protected $layoutFactory;
    protected $pageFactory;
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $resultPage = $this->pageFactory->create();
        $content = $resultPage->getLayout()
            ->createBlock('Magedelight\Ga4\Block\Adminhtml\View')->setTemplate('Magedelight_Ga4::view.phtml')->setData('row_id', $postData['id']);

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($content->toHtml());
    }
}
