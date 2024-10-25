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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ConfigObserver implements ObserverInterface
{
    /** @var RequestInterface  */
    private $request;

    /** @var WriterInterface  */
    private $configWriter;

    /**
     * General Configuration
     */
    protected const XML_PATH_GA4_GDPR_ENABLE = 'magedelight_ga4/general/gdpr_enable';

    private $scopeConfig;

    /**
     * ConfigObserver constructor.
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     */
    public function __construct(RequestInterface $request, WriterInterface $configWriter, ScopeConfigInterface $scopeConfig)
    {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $groupParams = $this->request->getParam('groups');
        $gdpr_enable = $this->isGTMStatus();
        if(isset($groupParams['cookie']['fields'])){
            $cookie = $groupParams['cookie']['fields'];
            if (isset($cookie['cookie_restriction']['value'])) {
                if ($cookie['cookie_restriction']['value'] != $gdpr_enable) {
                    $this->setData(
                        'magedelight_ga4/general/gdpr_enable',
                        $cookie['cookie_restriction']['value']
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @param $path
     * @param $value
     */
    public function setData($path, $value)
    {
        $this->configWriter->save(
            $path,
            $value,
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );
    }

    /**
     * GetConfig
     *
     * @param Config_path $config_path
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * IsGTMStatus
     */
    public function isGTMStatus()
    {
        return $this->getConfig(self::XML_PATH_GA4_GDPR_ENABLE);
    }
}
