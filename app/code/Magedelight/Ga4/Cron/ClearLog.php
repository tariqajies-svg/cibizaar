<?php
/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
 
namespace Magedelight\Ga4\Cron;

use Magedelight\Ga4\Model\GtagFactory;
use Magedelight\Ga4\Helper\Data as HelperData;

class ClearLog
{
    /**
     * @var GtagFactory
     */
    private $gtag;

    /**
     * @var HelperData
     */
    private $datahelper;

    /**
     * @param GtagFactory $gtag
     * @param HelperData $dataHelper
     */
    public function __construct(
        HelperData $dataHelper,
        GtagFactory $gtag
    ) {
        $this->gtag = $gtag;
        $this->datahelper = $dataHelper;
    }

    /**
     * magedelight_ga4 clear for Cron request
     *
     * @return RedirectFactory
     */
    public function execute()
    {
        
        if ($this->datahelper->getCronStatus()) {
            $date = date('Y-m-d'); //today date
        
            $weekOfdays = [];
            for ($i =1; $i <= $this->datahelper->getCronTime(); $i++) {
                $date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
                $weekOfdays = date('Y-m-d', strtotime($date));
            }

            $gtag  = $this->gtag->create()->getCollection()->addFieldToFilter('created_at', ['lt' => $weekOfdays]);
            try {
                $gtag->walk('delete');
                //$this->messageManager->addSuccess(__('The gtag eveng log has been cleared.'));
            } catch (\Exception $e) {
                //$this->messageManager->addError($e->getMessage());
            }
        }
        return $this;
    }
}
