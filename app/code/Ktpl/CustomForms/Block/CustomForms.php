<?php

namespace Ktpl\CustomForms\Block;
use Magento\Framework\Session\SessionManagerInterface;

class CustomForms extends \Magento\Framework\View\Element\Template
{

    protected $sessionManager;
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        SessionManagerInterface $sessionManager,
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->formKey = $formKey;
        $this->customerSession = $customerSession;
        $this->_sessionFactory = $sessionFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    public function getFormAction()
    {
        return $this->getUrl('customforms/index/customforms', ['_secure' => true]);
    }
    public function getCustomerGroup()
	{
		return $this->createSession()->getCustomer()->getData('group_id');
	}
    public function createSession()
	{
		return $this->_sessionFactory->create();
	}
	public function isLoggedIn()
	{
		//$customer = $this->_sessionFactory->create();
		return $this->createSession()->isLoggedIn();
	}
    public function getRandomNumber()
    {
        // Generate a random number
        return mt_rand(100000, 999999);
    }

    public function setSessionValue()
    {
        $this->sessionManager->start();
        
        // Set the random number in the session
        $randomValue = mt_rand(100000, 999999);
        // Set the new session value
        $this->sessionManager->setData('custom_session_value', $randomValue);
    }

    public function getSessionValue()
    {
        // Get the session value
        return $this->sessionManager->getData('custom_session_value');
    }
    public function setSessionValueRequestQuote()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_requestQuote');
        // Set the random number in the session
        $randomValue = mt_rand(100000, 999999);
        /*$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_form_request_random.log');
        $logger = new \Zend_Log();$logger->addWriter($writer);
        $logger->info('random number request quote');
        $logger->info($randomValue);*/
        // Set the new session value
        
        $this->sessionManager->setData('custom_session_value_requestQuote', $randomValue);
        return $randomValue;
    }
    public function setSessionValuelayout()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_layout');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_layout', $randomValue);
        return $randomValue;
    }
    public function setSessionValueCreditapp()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_creditapp');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_creditapp', $randomValue);
        return $randomValue;
    }
    public function setSessionValueTaxexempt()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_taxexempt');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_taxexempt', $randomValue);
        return $randomValue;
    }
    public function setSessionValueWarranty()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_warranty');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_warranty', $randomValue);
        return $randomValue;
    }
    public function setSessionValueMfgonboard()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_mfgonboard');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_mfgonboard', $randomValue);
        return $randomValue;
    }
    public function setSessionValueTrade()
    {
        $this->sessionManager->start();
        $this->sessionManager->unsetData('custom_session_value_trade');
        
        $randomValue = mt_rand(100000, 999999);

        $this->sessionManager->setData('custom_session_value_trade', $randomValue);
        return $randomValue;
    }
   
}