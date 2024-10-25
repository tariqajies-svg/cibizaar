<?php

namespace Ktpl\Quote\Controller\Contact;

/**
* Resuest Quote controller
*
* @method \Magento\Framework\App\RequestInterface getRequest()
* @method \Magento\Framework\App\Response\Http getResponse()
* @SuppressWarnings(PHPMD.CouplingBetweenObjects)
*/
class QuoteIndex extends \Magento\Framework\App\Action\Action
{
    /**
    * Recipient email config path
    */
    const XML_PATH_EMAIL_RECIPIENT = 'ktpl_quote/email/recipient_email';

    /**
    * Sender email config path
    */
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_general/email';

    /**
    * Sender name config path
    */

    const XML_PATH_SENDER_NAME = 'trans_email/ident_general/name';

    /**
    * Email template config path
    */
    const XML_PATH_EMAIL_TEMPLATE = 'ktpl_quote/email/template';

    protected $session;

    /**
    * @var \Magento\Framework\Json\Helper\Data $helper
    */
    protected $helper;

    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;

    
    /**
    * @var \Magento\Framework\Json\Helper\Data $jsonHelper
    */
    protected $jsonHelper;
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;    
    /**
    * @var \Magento\Framework\DataObject
    */
    protected $dataObject;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ktpl\Quote\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\DataObject $dataObject,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);        
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;      
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->dataObject = $dataObject;
        $this->_messageManager = $messageManager;
    }
    public function execute()
    {
        $data = $this->jsonHelper->jsonDecode($this->getRequest()->getContent());

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $response = [
            'errors' => false,
            'message' => __('Your request successfully submitted')
        ];
        try { 
            
            $error = false;
            $errorMsg=[];
            if (!\Laminas\Validator\StaticValidator::execute(trim($data['name']), 'NotEmpty')) {
                $error = true;
                $errorMsg[]=__('Name is Required');
            }
            if (!\Laminas\Validator\StaticValidator::execute(trim($data['phone']), 'NotEmpty')) {
                $error = true;
                $errorMsg[]=__('Mobile Number is Required');
            }
            if (!\Laminas\Validator\StaticValidator::execute(trim($data['email']), 'EmailAddress')) {
                $error = true;
                $errorMsg[]=__('email is Required');
            }

            if (!\Laminas\Validator\StaticValidator::execute(trim($data['comment']), 'NotEmpty')) {
                $error = true;
                $errorMsg[]=__('comment is Required');
            }
            if($error && !empty($errorMsg)){
                $response['errors']=true;
                $response['message']= __(implode(',',$errorMsg));
                return $resultJson->setData($response);
            }
            $dataObject = $this->dataObject;
            $dataObject->setData($data);

            if (empty($from)) {
                $from = [
                    'name' => $this->helper->getConfigValue(self::XML_PATH_SENDER_NAME),
                    'email' => $this->helper->getConfigValue(self::XML_PATH_EMAIL_SENDER),
                ];
            }

            $transport = $this->_transportBuilder
            ->setTemplateIdentifier($this->helper->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE))
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->helper->getStore()->getId(),
                ]
            )
            ->setTemplateVars(['data' => $dataObject])
            ->setFrom($from);
            
            $_emailsArr=[];
            if($_emails=$this->helper->getConfigValue(self::XML_PATH_EMAIL_RECIPIENT)){
                $_emailsArr=explode(',',$_emails);

                try{
                    if(!empty($_emailsArr)){
                        foreach($_emailsArr as $emailAddress){
                            $transport->addTo($emailAddress);
                        }
                        $transport ->setReplyTo($data['email']);
                        $transport->getTransport()->sendMessage();
                        $this->inlineTranslation->resume(); 
                    }
                }catch(\Exception $e){

                    $response = [
                        'errors' => true,
                        'message' => __($e->getMessage())
                    ];
                }


            }else{
                $response = [
                    'errors' => true,
                    'message' => __("Email receiptent doesn't set in admin configuration")
                ];
            }
           
            

        } catch (\Exception $e) {

            
            $response = [
                'errors' => true,
                'message' => __($e->getMessage())
            ];
        }
	if($response['errors']=false) {
            $this->_messageManager->addSuccess($response['message']);
            } else {
            $this->_messageManager->addError(__($response['message']));
        }
        return $resultJson->setData($response);
    } 
   
}
