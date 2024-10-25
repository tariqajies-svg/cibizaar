<?php
namespace Ktpl\Quote\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_sessionFactory = $sessionFactory;
        $this->_currentStore = $this->_storeManager->getStore();
    }

    public function getConfigValue($path,$storeId=null){
        $store=$storeId?$this->_storeManager->getStore($storeId):$this->_currentStore;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    public function getStore()
    {
        return $this->_currentStore;
    }
}