<?php
/**
 *
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

namespace Magedelight\Ga4\Plugin;

use Magedelight\Ga4\Helper\Data as DataHelper;
use Magedelight\Ga4\Model\CookieData;
use Magento\Framework\App\FrontController;

class ManagerCK
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magedelight\Ga4\Model\CookieData
     */
    protected $CookieData;

    /**
     * Construct
     *
     * @param DataHelper $dataHelper
     * @param CookieData $CookieData
     */
    public function __construct(DataHelper $dataHelper, CookieData $CookieData)
    {
        $this->dataHelper = $dataHelper;
        $this->CookieData = $CookieData;
    }

    /**
     * AfterDispatch
     *
     * @param FrontController $subject
     * @param result $result
     */
    public function afterDispatch(FrontController $subject, $result)
    {
        if ($this->dataHelper->isGTMStatus()) {
            $this->CookieData->setGa4Cookies();
        }
        return $result;
    }
}
