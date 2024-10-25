<?php

namespace Ktpl\CustomForms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Email Configuration.
     */
    const XML_PATH_CUSTOMFORMS_SENDER = 'general/customforms_email_sender';
    const XML_PATH_CUSTOMFORMS_ADMIN_MAIL = 'general/customforms_admin_mail';
    const XML_PATH_REQUEST_QUOTE_EMAIL_SEND = 'request_quote/allow_requotequote_mail';
    const XML_PATH_REQUEST_QUOTE_SUBJECT = 'request_quote/requestquote_subject';
    const XML_PATH_REQUEST_QUOTE_EMAIL = 'request_quote/requestquote_template';
    const XML_PATH_REQUEST_QUOTE_EMAIL_BCC = 'request_quote/requotequote_copyto';
    const XML_PATH_PUBLIC_KEY = 'recaptcha_frontend/type_recaptcha_v3/public_key';
    const XML_PATH_PRIVATE_KEY = 'recaptcha_frontend/type_recaptcha_v3/private_key';


    /**
    * @var Curl
    */
    protected $curl;
    
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    public function getMediaUrl()
    {
        return $this->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getBaseUrl()
    {
        return $this->getStore()->getBaseUrl();
    }

    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    public function getScopeValue($scopePath, $storeId = 0)
    {
        return $this->scopeConfig->getValue('custom_form/' . $scopePath, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRecaptchaSiteKey($storeId = 0)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getRecaptchaSecretKey($storeId = 0)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRIVATE_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }
    
    Public function getRecapcthaResponse($gRecaptchaResponse)
    {
        $recaptchaResponse = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->getRecaptchaSecretKey()."&response=".$gRecaptchaResponse."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
        
        return $recaptchaResponse;
    }
    /**
     * @return mixed
     */
    public function isRequestQuoteEmailSend($storeId = 0)
    {
        return $this->getScopeValue(self::XML_PATH_REQUEST_QUOTE_EMAIL_SEND, $storeId);
    }

    

    public function checkHtmlTags($param)
    {   
        if (strpos($param, '<h6') !== false || strpos($param, '&lt;h6') !== false || strpos($param, '<h5') !== false || strpos($param, '&lt;h5') !== false || strpos($param, '<h4') !== false || strpos($param, '&lt;h4') !== false || strpos($param, '<h3') !== false || strpos($param, '&lt;h3') !== false || strpos($param, '<h2') !== false || strpos($param, '&lt;h2') !== false || strpos($param, '<h1') !== false || strpos($param, '&lt;h1') !== false  || strpos($param, '<script') !== false || strpos($param, '&lt;script') !== false || strpos($param, '<html') !== false || strpos($param, '&lt;html') !== false || strpos($param, '&lt;iframe') !== false || strpos($param, '&lt;iframe') !== false || strpos($param, '&lt;style') !== false || strpos($param, '<style') !== false || strpos($param, '<iframe') !== false || strpos($param, '<frame') !== false) {
            return true;
        }
        return false;
    }

    public function checkHtmlTagsForDescription($param)
    {   
        if (strpos($param, '<h6') !== false || strpos($param, '&lt;h6') !== false || strpos($param, '<h5') !== false || strpos($param, '&lt;h5') !== false || strpos($param, '<h4') !== false || strpos($param, '&lt;h4') !== false || strpos($param, '<h3') !== false || strpos($param, '&lt;h3') !== false || strpos($param, '<h2') !== false || strpos($param, '&lt;h2') !== false || strpos($param, '<h1') !== false || strpos($param, '&lt;h1') !== false  || strpos($param, '<script') !== false || strpos($param, '&lt;script') !== false || strpos($param, '<html') !== false || strpos($param, '&lt;html') !== false || strpos($param, '&lt;iframe') !== false || strpos($param, '&lt;iframe') !== false || strpos($param, '&lt;style') !== false || strpos($param, '<style') !== false || strpos($param, '<iframe') !== false || strpos($param, '<frame') !== false) {
            return true;
        }
        return false;
    }

    public function checkFormFields($params)
    {
        if (array_key_exists('name', $params)) {
            if($this->checkHtmlTags($params['name']))
            {  
                return true;
            }
        }
        if (array_key_exists('ordernumber', $params)) {
            if($this->checkHtmlTags($params['ordernumber']))
            {  
                return true;
            }
        }
        if (array_key_exists('timeframe', $params)) {
            if($this->checkHtmlTags($params['timeframe']))
            {  
                return true;
            }
        }
        if (array_key_exists('companyname', $params)) {
            if($this->checkHtmlTags($params['companyname']))
            {   
                return true;
            }
        }
        if (array_key_exists('phonenumber', $params)) {
            if($this->checkHtmlTags($params['phonenumber']))
            {
                return true;
            }
        }
        if (array_key_exists('shippingaddress', $params)) {
            if($this->checkHtmlTags($params['shippingaddress']))
            {
                return true;
            }
        }
        if (array_key_exists('productlist', $params)) {
            if($this->checkHtmlTags($params['productlist']))
            {
                return true;
            }
        }
        if (array_key_exists('phone', $params)) {
            if($this->checkHtmlTags($params['phone']))
            {
                return true;
            }
        }
        if (array_key_exists('application', $params)) {
            if($this->checkHtmlTags($params['application']))
            {
                return true;
            }
        }
        if (array_key_exists('billingname', $params)) {
            if($this->checkHtmlTags($params['billingname']))
            {
                return true;
            }
        }
        if (array_key_exists('billingaddress', $params)) {
            if($this->checkHtmlTags($params['billingaddress']))
            {
                return true;
            }
        }
        if (array_key_exists('contact', $params)) {
            if($this->checkHtmlTags($params['contact']))
            {
                return true;
            }
        }
        if (array_key_exists('email', $params)) {
            if($this->checkHtmlTags($params['email']))
            {
                return true;
            }
        }
        if (array_key_exists('business', $params)) {
            if($this->checkHtmlTags($params['business']))
            {
                return true;
            }
        }
        if (array_key_exists('exemption', $params)) {
            if($this->checkHtmlTags($params['exemption']))
            {
                return true;
            }
        }
        if (array_key_exists('taxid', $params)) {
            if($this->checkHtmlTags($params['taxid']))
            {
                return true;
            }
        }
        if (array_key_exists('manufacturer', $params)) {
            if($this->checkHtmlTags($params['manufacturer']))
            {
                return true;
            }
        }
        if (array_key_exists('skucount', $params)) {
            if($this->checkHtmlTags($params['skucount']))
            {
                return true;
            }
        }
        if (array_key_exists('freight', $params)) {
            if($this->checkHtmlTags($params['freight']))
            {
                return true;
            }
        }
        if (array_key_exists('opening', $params)) {
            if($this->checkHtmlTags($params['opening']))
            {
                return true;
            }
        }
        if (array_key_exists('orderfee', $params)) {
            if($this->checkHtmlTags($params['orderfee']))
            {
                return true;
            }
        }
        if (array_key_exists('fob_locations', $params)) {
            if($this->checkHtmlTags($params['fob_locations']))
            {
                return true;
            }
        }
        if (array_key_exists('corp_location', $params)) {
            if($this->checkHtmlTags($params['corp_location']))
            {
                return true;
            }
        }
        if (array_key_exists('company', $params)) {
            if($this->checkHtmlTags($params['company']))
            {
                return true;
            }
        }
        if (array_key_exists('tax', $params)) {
            if($this->checkHtmlTags($params['tax']))
            {
                return true;
            }
        }
        if (array_key_exists('return', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['return'])))
            {  
                return true;
            }
        }
        if (array_key_exists('additional', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['additional'])))
            {  
                return true;
            }
        }
        if (array_key_exists('spaceinformation', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['spaceinformation'])))
            {  
                return true;
            }
        }
        if (array_key_exists('fixture', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['fixture'])))
            {  
                return true;
            }
        }
        if (array_key_exists('defective', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['defective'])))
            {  
                return true;
            }
        }
        if (array_key_exists('symptoms', $params)) {
            if($this->checkHtmlTagsForDescription(trim($params['symptoms'])))
            {  
                return true;
            }
        }
    }

}
