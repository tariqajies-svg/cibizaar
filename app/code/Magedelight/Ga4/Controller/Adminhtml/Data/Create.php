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

class Create extends \Magedelight\Ga4\Controller\Adminhtml\Event\Generate
{
    /**
     * Execute
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $jsonUrl = null;
        $msg = $this->_validateParams($params);

        $formData = [];
        parse_str($params['form_data'], $formData);

        if (!count($msg)) {
            try {
                $jsonUrl = $this->modelJson->
                generateItemJson(
                    trim($params['account_id']),
                    trim($params['container_id']),
                    trim($params['tracking_id']),
                    trim($params['public_id'])
                );
                $msg[]=__('File successfully generated.You can download the file by clicking on the Download button.');
            } catch (\Exception $ex) {
                $msg[] = $ex->getMessage();
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'msg' => $msg,
            'jsonUrl' => $jsonUrl
        ]);
        return $resultJson;
    }

    /**
     * ValidateParams
     *
     * @param Params $params
     */
    protected function _validateParams($params)
    {
        $accountId = $params['account_id'];
        $containerId = $params['container_id'];
        $trackingId = $params['tracking_id'];
        $publicId = $params['public_id'];

        $message = [];

        if (!strlen(trim($accountId))) {
            $message[] = __('Account ID must be specified');
        }

        if (!strlen(trim($containerId))) {
            $message[] = __('Container ID must be specified');
        }

        if (!strlen(trim($trackingId))) {
            $message[] = __('Universal Tracking ID must be specified');
        }

        if (!strlen(trim($publicId))) {
            $message[] = __('Public ID must be specified');
        }

        return $message;
    }
}
