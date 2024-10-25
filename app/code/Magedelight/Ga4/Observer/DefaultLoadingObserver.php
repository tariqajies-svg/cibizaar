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

namespace Magedelight\Ga4\Observer;

use Magedelight\Ga4\Helper\Data;
use Magento\Framework\Event\Observer;
use Magedelight\Ga4\Model\EventTrigger;

class DefaultLoadingObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param Data $mdhelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(Data $mdhelper, EventTrigger $eventTrigger)
    {
        $this->mdhelper = $mdhelper;
        $this->eventTrigger = $eventTrigger;
    }
    
    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->mdhelper->isGTMStatus()) {
            $layout = $observer->getData('element_name');

            if ($layout != 'magedelight_ga4_top') {
                return $this;
            }

            $transport = $observer->getData('transport');
            $html = $transport->getOutput();

            $jsScript = $this->eventTrigger->gtagCode();
            $html = $jsScript . PHP_EOL . $html;

            $transport->setOutput($html);
        }
        return $this;
    }
}
