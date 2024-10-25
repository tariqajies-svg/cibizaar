<?php

namespace SkyExLabel\Slabel\Controller\Adminhtml\Order;


class Skypdf extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $curl;
    protected $helperData;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \SkyExLabel\Slabel\Helper\Data $helperData
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->curl = $curl;
        $this->helperData = $helperData;
    }

    public function execute()
    {
        $skyex_key = $this->helperData->getGeneralConfig('skyex_key');

        if (!empty($skyex_key)) {


            $entity_id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('Magento\Sales\Model\Order');
            $loadedOrder = $model->load($entity_id);


            $SkyexAwb = $loadedOrder->getSkyexAwb();



            $skyex_key1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOiIxNTk3MDc1NTczIiwiaXNzIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiYXVkIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiZXhwIjoiMTY4MzQ3NTU3MyIsInVuYW1lIjoiUExVR0lOIiwidWlkIjoiZWVmOGNlYzBkZDAyNGFiOGIzOThiNGU0MmJlNGNiODYifQ.m_TaeHahbSOArjyQCvTtz0DBOTAePJixqeApoxIejbA';




            if(!empty($SkyexAwb)){


                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", 'Bearer '.$skyex_key);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->get('https://www.skyexpressinternational.com/api/AWB/'.$SkyexAwb);
                $response = $this->curl->getBody();


                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $labelGenerator = $objectManager->create('Magento\Shipping\Model\Shipping\LabelGenerator');





                $labelContent[] = $response; 
                $outputPdf = $labelGenerator->combineLabelsPdf($labelContent);

                $fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');

                $label = $fileFactory->create(
                 'Labels.pdf',
                 $outputPdf->render(),
                 \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                 'application/pdf'
             );

                $er = 'SkyEx Label downloaded successfully.';
                // $this->messageManager->addSuccess(__('SkyEx Label downloaded successfully.'));
            }else{
                $er = "Order doesn't have a SkyEx AWB number." ;
            // if(!empty($response['ModelError'])){
            //   foreach ($response['ModelError'] as $ModelError) {
            //     $er .= end($ModelError);
            // }
                $this->messageManager->addError(__($er));
            }
        }else{
            $this->messageManager->addError(__('Add your Skyex token in SkyEx configuration.'));
        }

        







        $resultRedirect = $this->resultRedirectFactory->create();
// $resultRedirect->setPath($this->getComponentRefererUrl());
// return $resultRedirect;
        return $resultRedirect->setPath('sales/order', array('_current' => true));

    }


}