<?php

namespace SkyExLabel\Slabel\Controller\Adminhtml\Order;


class Skypush extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $helperData;
    protected $curl;

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
        $ShipmentType = $this->helperData->getGeneralConfig('stype');
        $TotalWeight = $this->helperData->getGeneralConfig('dpw');

        if (!empty($ShipmentType)) {
            $ShipmentType = 1;
        }
        if (!empty($TotalWeight)) {
            $TotalWeight = 1;
        }

        if (!empty($skyex_key)) {

            $entity_id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('Magento\Sales\Model\Order');
            $loadedOrder = $model->load($entity_id);

            $SkyexAwb = $loadedOrder->getSkyexAwb();

             


            if (empty($SkyexAwb)) {

                $ShipmentType = 1;

// $SkyexLabel = $loadedOrder->getSkyexLabel();
                $increment_id = $loadedOrder->getIncrementId();
                $entity_id = $loadedOrder->getEntityId();
                $grand_total = $loadedOrder->getGrandTotal();
                $total_qty_ordered = $loadedOrder->getTotalQtyOrdered();
                $order_currency_code = $loadedOrder->getOrderCurrencyCode();
                $weight = $loadedOrder->getWeight();
                    if (!empty($weight)) {
                        $TotalWeight = $weight;
                    }


                $payment = $loadedOrder->getPayment();
                $method = $payment->getMethodInstance();
                $payment_method = $method->getTitle();


                $address1 = $loadedOrder->getShippingAddress()->getData();
                $city = $address1['city'];
                $region = $address1['region'];
                $postcode = $address1['postcode'];
                $prefix = $address1['prefix'];
                $firstname = $address1['firstname'];
                $middlename = $address1['middlename'];
                $lastname = $address1['lastname'];
                $street = $address1['street'];
                $telephone = $address1['telephone'];
                $country_id = $address1['country_id'];
                $company = $address1['company'];
                // $address1 = json_encode($address1);


                $full_address = $street.','.$region.','.$city;
                    $address1 = substr($full_address,0,50);
                    $full_addressr = substr($full_address,50);
                    $address2 = substr($full_addressr,0,50);
                    $address3 = substr($full_address,100);

                    if (!empty($address1)) {
                        $address1 = $address1;
                    }else{
                        $address1 = '';
                    }
                    if (!empty($address2)) {
                        $address2 = $address2;
                    }else{
                        $address2 = '';
                    }
                    if (!empty($address3)) {
                        $address3 = $address3;
                    }else{
                        $address3 = '';
                    }

                    $Length = 0;
                    $Width = 0;
                    $Height = 0;
                    if($ShipmentType == 2){
                        if(empty($TotalWeight)){ $TotalWeight = 1; }
                        $dim_w = $TotalWeight * 5000;
                        $dim = pow($dim_w,1/3);
                        $Length = $dim;
                        $Width = $dim;
                        $Height = $dim;
                    }


                $skyex_key1 = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOiIxNTk3MDc1NTczIiwiaXNzIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiYXVkIjoiaHR0cHM6Ly93d3cuc2t5ZXhwcmVzc2ludGVybmF0aW9uYWwuY29tIiwiZXhwIjoiMTY4MzQ3NTU3MyIsInVuYW1lIjoiUExVR0lOIiwidWlkIjoiZWVmOGNlYzBkZDAyNGFiOGIzOThiNGU0MmJlNGNiODYifQ.m_TaeHahbSOArjyQCvTtz0DBOTAePJixqeApoxIejbA';

                $params = array(
                    'ClientReference' => $entity_id,
                    'ConsigneeName' => $firstname,
                    'ConsigneeName2' => $lastname,
                    'ConsigneeCompanyName' => $company,
                    'ConsigneeAddress1' => $address1,
                    'ConsigneeAddress2' => $address2,
                    'ConsigneeAddress3' => $address3,
                    'ConsigneeCity' => $city,
                    'ConsigneeState' => $region,
                    'ConsigneePostalCode' => $postcode,
                    'ConsigneeCountryCode' => $country_id,
                    'ConsigneeTelephone1' => $telephone,
                    'TotalWeight' => $TotalWeight,
                    'ItemsCount' => round($total_qty_ordered),
                    'ShipmentType' => $ShipmentType,
                    'Length' => $Length,
                    'Width' => $Width,
                    'Height' => $Height,
                    'ConsignmentValueCurrencyCode' => $order_currency_code,
                    'ConsignmentValue' => $grand_total,
                );

                if($payment_method == 'Cash On Delivery'){
                $params['CODValue'] = $grand_total;
                $params['CODValueCurrencyCode'] = $order_currency_code;
              }




                $params = json_encode($params);

                $this->curl->addHeader("Content-Type", "application/json");
                $this->curl->addHeader("Authorization", 'Bearer '.$skyex_key);
                $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
                $this->curl->post('https://www.skyexpressinternational.com/api/Booking', $params);
                $result = $this->curl->getBody();
                $response = json_decode($result, true);


                if(!empty($response['AWBNo'])){

                    $model->setData('skyex_awb',$response['AWBNo']);
    // $model->save();

    // $this->curl->addHeader("Content-Type", "application/json");
    // $this->curl->addHeader("Authorization", 'Bearer '.$skyex_key);
    // $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
    // $this->curl->get('https://www.skyexpressinternational.com/api/AWB/'.$response['AWBNo']);
    // $lresult = $this->curl->getBody();


    // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    // $labelGenerator = $objectManager->create('Magento\Shipping\Model\Shipping\LabelGenerator');

    // if (!empty($SkyexLabel)) {
    //     $lresult = base64_decode($SkyexLabel);
    // }


    // $labelContent[] = base64_decode((string) $lresult); 

// $labelContent[] = $lresult; 
// $labelContentjson = base64_encode((string) $lresult);
// $outputPdf = $labelGenerator->combineLabelsPdf($labelContent);

// $fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');

// $label = $fileFactory->create(
//     'Labels.pdf',
//     $outputPdf->render(),
//     \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
//     'application/pdf'
// );




// $model->setData('skyex_label',$labelContentjson);
                    $model->save();
                    $er = 'Order pushed successfully';
                    $this->messageManager->addSuccess(__('Order pushed successfully.'));
                }else{
                    $er = 'Please fix the errors and then try again.';
                    if(!empty($response['ModelError'])){
                      foreach ($response['ModelError'] as $ModelError) {
                        $er .= end($ModelError);
                    }
                }

                $this->messageManager->addError(__($er));
            }
        }else{
            $this->messageManager->addError(__('Order has already pushed.'));
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