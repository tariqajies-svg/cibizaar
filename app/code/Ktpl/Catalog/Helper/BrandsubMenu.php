<?php
namespace Ktpl\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magebit\HyvaTheme\ViewModel\BrandsNavigation;
use Magebit\HyvaTheme\ViewModel\Navigation;

class BrandsubMenu extends AbstractHelper
{


   public function __construct(
      BrandsNavigation $brandsNavigation,
      Navigation $navigation
   ) {
      $this->brandsNavigation = $brandsNavigation;
      $this->navigation = $navigation;
   }

   public function getBrandSubMenuArray(){ 

      
$navigation =  $this->navigation;
$brandsNavigation = $this->brandsNavigation;
/* Prepare main category menu data */

$rootCategoryId = $navigation->getRootCategoryId();
$mainCategoryId = $navigation->getMainCategoryId() ?: null;
$brandsCategoryId = $brandsNavigation->getBrandsCategoryId() ?: null;

$categoryMenu = $navigation->getCustomNavigation([$rootCategoryId, $mainCategoryId], 3);
$subMenuItems = $navigation->getCustomNavigation([$rootCategoryId, $mainCategoryId], 5, 4);




      /* Prepare brand category menu data */
      $brandMenu = $navigation->getCustomNavigation([$rootCategoryId, $brandsCategoryId], 3);
      $brandSubMenuItems = $brandsNavigation->getCustomNavigation([$rootCategoryId, $brandsCategoryId], 5, 4, $brandMenu);
      $brandCategoryArray =  array();
      $brandMenuData = $brandMenu;
      foreach($brandMenu as $brand) {

            $categoryId = $brand['id'];  
            $newBrandSubMenuItems =  array();
            $newBrandSubMenuItemsFlag = false;

            foreach($brandSubMenuItems as $key=>$brandSubMenuItemsArray) {

               if($brandSubMenuItemsArray['childData']) {
                   
                   $newbrandSubMenuItemsArrayChildData = array();
                   $newbrandSubMenuItemsArrayChildFlag = false;
                   // echo "<pre>";Print_r($brandSubMenuItemsArray);die('1-if');
                   foreach($brandSubMenuItemsArray['childData'] as $subkey=>$brandSubMenuItemsArrayChildData) {
                        foreach($brandSubMenuItemsArrayChildData['parent_ids'] as $parentId) {

                              if($parentId == $categoryId) {
                                 $newbrandSubMenuItemsArrayChildFlag = true;
                                 $newBrandSubMenuItemsFlag = true; 
                                 break;
                              }
                        }
                        if($newbrandSubMenuItemsArrayChildFlag){
                           
                           $newbrandSubMenuItemsArrayChildData[$subkey] = $brandSubMenuItemsArrayChildData;
                           $newbrandSubMenuItemsArrayChildFlag = false;
                        }               
                   }
                     if($newBrandSubMenuItemsFlag){ 
                        $newBrandSubMenuItems[$brandSubMenuItemsArray['id']] = $brandSubMenuItemsArray;
                        $newBrandSubMenuItems[$brandSubMenuItemsArray['id']]['childData'] = $newbrandSubMenuItemsArrayChildData;
                        $newBrandSubMenuItemsFlag = false;
                      }
               } else {
                  foreach($brandSubMenuItemsArray['parent_ids'] as $parentId) {

                              if($parentId == $categoryId) {
                                 $newbrandSubMenuItemsArrayChildFlag = true;
                                 $newBrandSubMenuItemsFlag = true;
                                 break;
                              }
                        }
                        if($newBrandSubMenuItemsFlag) {
                        $newBrandSubMenuItems[$brandSubMenuItemsArray['id']] = $brandSubMenuItemsArray;
                        $newBrandSubMenuItems[$brandSubMenuItemsArray['id']]['childData'] = $newbrandSubMenuItemsArrayChildData;
                        $newBrandSubMenuItemsFlag = false;
                        $newbrandSubMenuItemsArrayChildFlag = false;
                      }
                  // if($brandSubMenuItemsArray['parent_id'] = $categoryId) {
                  //    $newBrandSubMenuItems[$brandSubMenuItemsArray['id']] = $brandSubMenuItemsArray;
                  //    $newBrandSubMenuItemsFlag = false;
                  //    $newbrandSubMenuItemsArrayChildFlag = false;
                  // }
               }

            }
            $brandMenuData[$brand['id']]['childData'] = $newBrandSubMenuItems;
         }
   return $brandMenuData;
   }
  
}
