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

namespace Magedelight\Ga4\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\SerializerInterface;

class View extends Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magedelight\Ga4\Model\ResourceModel\Gtag\CollectionFactory $logCollection
    ) {
        $this->logCollection = $logCollection;
        parent::__construct($context);
    }

    public function rowData()
    {
        $logData = $this->logCollection->create()->addFieldToFilter('entity_id', $this->getRowId());
        foreach ($logData->getData() as $key => $rowsData) {
            $rowDetail = [
            'entityId' => $rowsData['entity_id'],
            'pageUrl' => $rowsData['event_label'],
            'eventData' => $rowsData['event_data']
            ];
        }
        return $rowDetail;
    }
}
