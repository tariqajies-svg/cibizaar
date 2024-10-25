<?php

namespace SkyExLabel\Slabel\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Class MassDelete
 */
class MassSkyexpdf extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    protected $curl;

    protected $helperData;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        Curl $curl,
        \SkyExLabel\Slabel\Helper\Data $helperData
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->curl = $curl;
        $this->helperData = $helperData;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $skyex_key = $this->helperData->getGeneralConfig('skyex_key');

        if (!empty($skyex_key)) {

            $model = $this->_objectManager->create('Magento\Sales\Model\Order');
            $er = '';
            $er1 = 0;
            $labelContent = array();

            $skyex_key1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOiIxNTk3MDc1NTczIiwiaXNzIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiYXVkIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiZXhwIjoiMTY4MzQ3NTU3MyIsInVuYW1lIjoiUExVR0lOIiwidWlkIjoiZWVmOGNlYzBkZDAyNGFiOGIzOThiNGU0MmJlNGNiODYifQ.m_TaeHahbSOArjyQCvTtz0DBOTAePJixqeApoxIejbA';


            foreach ($collection->getItems() as $order) {
                if (!$order->getEntityId()) {
                    continue;
                }
                $increment_id = $order->getIncrementId();
                $SkyexAwb = $order->getSkyexAwb();

                
                if(!empty($SkyexAwb)){

                    $this->curl->addHeader("Content-Type", "application/json");
                    $this->curl->addHeader("Authorization", 'Bearer '.$skyex_key);
                    $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                    $this->curl->get('https://www.skyexpressinternational.com/api/AWB/'.$SkyexAwb);
                    $response = $this->curl->getBody();
            // $lresponse = json_decode($lresult, true);


                // $labelContent[] = base64_decode((string) $lresult); 
                    $labelContent[] = $response; 
                    



                }else{
                    $er1 = $er1+1;
                    $er .= $increment_id.": Order doesn't have a SkyEx AWB number.";
                // if(!empty($response['ModelError'])){
                //   foreach ($response['ModelError'] as $ModelError) {
                //     $er .= end($ModelError);
                // }
                }
            }

        }else{
            $er = 'Add your Skyex token in SkyEx configuration.';
        }
        





        if($er1 == 0)
        {
            // $er = 'SkyEx Label downloaded successfully.';
            // $this->messageManager->addSuccess(__('SkyEx Label downloaded successfully.'));
        }else{
            $this->messageManager->addError(__('Please fix the errors in below orders and then try again'.$er));
        }


        if (!empty($labelContent)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $labelGenerator = $objectManager->create('Magento\Shipping\Model\Shipping\LabelGenerator');

            $outputPdf = $labelGenerator->combineLabelsPdf($labelContent);

            $fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');

            $label = $fileFactory->create(
             'SkyEx-Labels.pdf',
             $outputPdf->render(),
             \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
             'application/pdf'
         );
        }





        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}