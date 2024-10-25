<?php


namespace Ktpl\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{


   public function __construct(
      \Magento\Catalog\Model\Category $category,
      \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
      \Magento\Customer\Model\Session $customerSession
   )
   {
      $this->category = $category;
      $this->groupRepository = $groupRepository;
      $this->_customerSession = $customerSession;
   }


    public function getCategoryName($categoryId)
    {
      $categoryIdList = array("380", "740", "765", "758");
      $result = array_diff($categoryId,$categoryIdList);
        if(count($result)==0) {
            $result = $categoryId;
        }
        $cat = $this->category->load(end($result));
        if($cat) {
           return $cat->getName();
        }
      }
      
      public function getGroupId(){
      if($this->_customerSession->isLoggedIn()) {
         $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        return $this->groupRepository->getById($customerGroupId)->getCode();
     } else {
      return false;
     }
   }
  
}

