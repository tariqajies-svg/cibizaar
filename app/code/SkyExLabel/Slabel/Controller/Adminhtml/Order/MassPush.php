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
class MassPush extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        $ShipmentType = $this->helperData->getGeneralConfig('stype');
        $TotalWeight = $this->helperData->getGeneralConfig('dpw');

        if (!empty($ShipmentType)) {
            $ShipmentType = 1;
        }
        if (!empty($TotalWeight)) {
            $TotalWeight = 1;
        }

        $model = $this->_objectManager->create('Magento\Sales\Model\Order');
        $er = '';
        $er1 = 0;
        if (!empty($skyex_key)) {



            foreach ($collection->getItems() as $order) {

                if (!$order->getEntityId()) {
                    continue;
                }
                $loadedOrder = $model->load($order->getEntityId());
                $SkyexAwb = $loadedOrder->getSkyexAwb();
                $increment_id = $order->getIncrementId();
                if (empty($SkyexAwb)) {


            // $loadedOrder->delete();

            // $SkyexLabel = $order->getSkyexLabel();

                    $entity_id = $order->getEntityId();
                    $grand_total = $order->getGrandTotal();
                    $total_qty_ordered = $order->getTotalQtyOrdered();
                    $order_currency_code = $order->getOrderCurrencyCode();
                    $weight = $order->getWeight();
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

                    }else{
                        $er1 = $er1+1;
                        $er .= $increment_id.': ';
                        if(!empty($response['ModelError'])){
                          foreach ($response['ModelError'] as $ModelError) {
                            $er .= end($ModelError);
                        }
                    }
                }
            }else{
                $er1 = $er1+1;
                $er .= $increment_id.': ';
                $er .= 'Order has already pushed. ';
            }


        }
    }else{
        $er = 'Add your Skyex token in SkyEx configuration.';
    }


        // }else{
        //       $er1 = $er1+1;
        //       $er .= $post_id.' : ';
        //       $er .= 'Order has already pushed. ';
        //     }

    if($er1 == 0)
    {
        $er = 'Orders pushed successfully';
        $this->messageManager->addSuccess(__('Orders pushed successfully. '));
    }else{
        $this->messageManager->addError(__('Please fix the errors in below orders and then try again. '.$er));
    }



    $resultRedirect = $this->resultRedirectFactory->create();
    $resultRedirect->setPath($this->getComponentRefererUrl());
    return $resultRedirect;
}
}