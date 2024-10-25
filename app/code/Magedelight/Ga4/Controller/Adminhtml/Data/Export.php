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

namespace Magedelight\Ga4\Controller\Adminhtml\Data;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magedelight\Ga4\Model\CreatedData;

class Export extends Action
{
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $http;

    /**
     * @var \Magedelight\Ga4\Model\CreatedData
     */
    protected $jsonGenerator;

     /**
      * Construct
      *
      * @param Context $context
      * @param Http $http
      * @param CreatedData $jsonGenerator
      */
    public function __construct(
        Context $context,
        Http $http,
        CreatedData $jsonGenerator
    ) {
        parent::__construct($context);
        $this->http = $http;
        $this->jsonGenerator = $jsonGenerator;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $response = $this->jsonGenerator->getCreatedJsonData();
        $this->http->getHeaders()->clearHeaders();
        $this->http->setHeader('Content-Type', 'application/json')
            ->setHeader("Content-Disposition", "attachment; filename=ga4Export.json")
            ->setBody($response);
    }
}
