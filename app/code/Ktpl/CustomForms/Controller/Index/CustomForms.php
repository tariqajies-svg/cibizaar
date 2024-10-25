<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\CustomForms\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Action\Context;
use Ktpl\CustomForms\Helper\Data as DataHelper;
use Ktpl\CustomForms\Model\Service\EmailService;
use Ktpl\CustomForms\Model\Service\EmailServiceFactory;
use Magento\Framework\DataObject;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class CustomForms extends \Magento\Framework\App\Action\Action
{
    const ATTACH_LOCATION = 'formsAttachment/';
    const TAX_ATTACH_LOCATION = 'formsTaxAttachment/';
    /**
     * @var Validator
     */
    protected $formKeyValidator;

    protected $uploaded = false;
    protected $request;
    protected $cookieManager;
    protected $sessionManager;

    protected $messageManager;

    /**
     * @param Context $context
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        ManagerInterface $messageManager,
        DataHelper $helper,
        EmailServiceFactory $emailService,
        UploaderFactory $fileUploaderFactory,
        Filesystem $fileSystem,
        File $file,
        Http $request,
        CookieManagerInterface $cookieManager,
        SessionManagerInterface $sessionManager
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->emailService = $emailService;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        $this->request = $request;
        $this->cookieManager = $cookieManager;
        $this->sessionManager = $sessionManager;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NotFoundException
     */
    public function execute()
    {  
        
        $postData = $this->getRequest()->getPostValue();

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom_form_request.log');
        $logger = new \Zend_Log();$logger->addWriter($writer);
        $logger->info('text message');
        $logger->info(json_encode($postData));

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

       
        
        if (!$this->formKeyValidator->validate($this->getRequest()) || !$this->getRequest()->isPost() || !array_key_exists('username', $postData) /* || !array_key_exists('g-recaptcha-response', $postData)*/) {
            
            $message = __('Invalid Form Key');
            $this->messageManager->addError($message);
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        
        //// Server side check validation for special charector are not allow        
        if($this->helper->checkFormFields($postData))
        {   
            $this->messageManager->addError('The field should not contain script , iframe , style , html tags..');
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        ////////////////////////////////////////////////
        try {
            $subject = $mailTemplate = $message = '';            
            if($this->getRequest()->getParam('form_name') == 'request_quote')
            {
                // $message = 'Thank you! We will get back to you as soon as possible with an answer or resolution.';
                if($this->helper->isRequestQuoteEmailSend($this->helper->getStore()->getId()))
                {   
                    $subject = $this->helper->getScopeValue($this->helper::XML_PATH_REQUEST_QUOTE_SUBJECT, $this->helper->getStore()->getId());
                    $mailTemplate = EmailService::EMAIL_REQUEST_QUOTE;
                    $message = 'Thank you! We will get back to you as soon as possible with an answer or resolution.';
                    // echo "message..." . $message;
                    // die("++++");
                }
            }

            if($subject && $mailTemplate && $message)
            {  
                $this->getRequest()->setPostValue('subject',$subject);

                $emailVars = ['data' => new DataObject($this->getRequest()->getParams())];

                $this->sendEmail($emailVars, $mailTemplate);
                
                $this->messageManager->addSuccessMessage(__($message));
            }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $message = __('There is some error to post data.');
            $this->messageManager->addException($e, $message);
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    public function sendEmail($emailVariable, $type)
    {   
        $attachement    = $this->getAttachment();
        //$taxAttachement = $this->getTaxAttachment();
        
        // $attachmentMerge = array_merge($attachement,$taxAttachement);
        $attachmentMerge = array_merge($attachement);

        $emailService = $this->emailService->create();
        $emailService->setStoreId($this->helper->getStore()->getId());
        $emailService->setTemplateVars($emailVariable);
        if ($this->uploaded && !empty($attachmentMerge)) {
            $emailService->setAttachment($attachmentMerge);
        }
        $emailService->setType($type);
        $email = $this->helper->getScopeValue($this->helper::XML_PATH_CUSTOMFORMS_ADMIN_MAIL, $this->helper->getStore()->getId());
        $emailService->setSendTo($email);
    
        $emailService->send();
        
    }

    public function getAttachment()
    {
        $filePath = null;
        $fileName = null;
        $filesUpload =  $attachmentData = [];
        
        $attachments = $this->getRequest()->getFiles('attachment');


        if(empty($attachments))
        {
            return $filesUpload;
        }
    
        foreach($attachments as $key => $value){
            $attachmentData[] = $value['name'];
        }

        try {
            $fileCheck = $this->fileUploaderFactory->create(['fileId' => 'attachment[0]']);
            $file = $fileCheck->validateFile();
            $attachment = $file['name'] ?? null;
            $attachment = $attachment;
        } catch (\Exception $e) {
            $attachment = null;
        }

        if ($attachment) {
            $i = 0;
            foreach ($attachments as $files) {
                if (isset($files['tmp_name']) && strlen($files['tmp_name']) > 0) {
                    //echo "<pre>";
                    //var_dump($files);
                    if($this->getRequest()->getParam('form_name') == 'creditapp'){
                        $attachments[$i]['name'] = 'creditreference-'.$attachments[$i]['name'];
                        $attachments[$i]['full_path'] = 'creditreference-'.$attachments[$i]['full_path'];
                    }
                    //var_dump($attachments[$i]['name']);
                  try {
                    $upload = $this->fileUploaderFactory->create(['fileId' => $attachments[$i]]);
                    $upload->setAllowRenameFiles(true);
                    $upload->setAllowedExtensions(['pdf', 'doc', 'xlsx', 'csv', 'txt']);
        
                    $path = $this->fileSystem
                        ->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath(self::ATTACH_LOCATION.$this->getRequest()->getParam('form_name'));
                    $result = $upload->save($path);
                    $this->uploaded = self::ATTACH_LOCATION.$this->getRequest()->getParam('form_name') . $upload->getUploadedFilename();
                    //var_dump($result);
                    if($this->getRequest()->getParam('form_name') == 'creditapp'){
                    $filesUpload[] = [
                        'path' => $result['path'] .'/'. $result['file'],
                        'name'=> $result['name']
                    ];
                    //var_dump($filesUpload);die();
                    }else{
                        $filesUpload[] = [
                            'path' => $result['path'] .'/'. $result['file'],
                            'name'=> $result['name']
                        ];
                    }
                    //$fileName[] = $result['name'];
                  }catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                  }
                }
                $i++;
            }
        }
        return $filesUpload;
    }


}
