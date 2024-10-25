<?php
/**
 *
 * Magedelight
 *
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */


namespace Magedelight\Ga4\ViewModel;

use Magedelight\Ga4\Model\EventTrigger;
use \Magento\Framework\DataObject;
use Magedelight\Ga4\Helper\Data;
use \Magento\Framework\Data\Helper\PostHelper;

class Item implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $datahelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var PostHelper
     */
    protected $posthelper;

    /**
     * Construct
     *
     * @param EventTrigger $eventTrigger
     * @param Data $datahelper
     * @param Http $request
     */
    public function __construct(
        EventTrigger $eventTrigger,
        Data $datahelper,
        PostHelper $postHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->eventTrigger = $eventTrigger;
        $this->datahelper = $datahelper;
        $this->posthelper = $postHelper;
        $this->request = $request;
    }

    /**
     * SelectItem
     *
     * @param Product $product
     * @param index $index
     * @param selected $selected
     * @param selectedId $selectedId
     */
    public function selectItem($product, $index = 0, $selected = '', $selectedId = '')
    {
        $html = '';
        $itemClickableSection = $this->eventTrigger->newSection('Manager', 'select_item.phtml');
        if ($itemClickableSection) {
            $itemClickableSection->setProduct($product);
            $itemClickableSection->setIndex($index);
            if (!$selected) {
                $fetchData = $this->datahelper->getCurrentCategory();
                if (!empty($fetchData)) {
                    $selected =  $this->datahelper->storeCat($fetchData);
                } else {
                    $getUrl = $this->request->getModuleName() .
                        DIRECTORY_SEPARATOR . $this->request->getControllerName() .
                        DIRECTORY_SEPARATOR . $this->request->getActionName();
                    switch ($getUrl) {
                        case 'catalogsearch/result/index':
                            $selected = __('Search Result');
                            break;
                        case 'catalogsearch/advanced/result':
                            $selected = __('Advanced Search Result');
                            break;
                    }
                }
            }
            if (!$selectedId) {
                $getCurrentCategory = $this->datahelper->getCurrentCategory();
                if (!empty($getCurrentCategory)) {
                    $selectedId = $getCurrentCategory->getId();
                } else {
                    $requestUrl = $this->request->getModuleName() .
                        DIRECTORY_SEPARATOR . $this->request->getControllerName() .
                        DIRECTORY_SEPARATOR . $this->request->getActionName();
                    switch ($requestUrl) {
                        case 'catalogsearch/advanced/result':
                            $selectedId = 'advanced_search_result';
                            break;
                        case 'catalogsearch/result/index':
                            $selectedId = 'search_result';
                            break;
                    }
                }
            }
            $itemClickableSection->setList($selected);
            $itemClickableSection->setListId($selectedId);
            $html = trim($itemClickableSection->toHtml());
            if (!empty($html)) {
                $htmlObj = new DataObject(['html' => $html]);
                $html = $htmlObj->getHtml();
                $html = 'onclick="' . $html . '"';
            }
            return $html;
        }
    }

    public function dataHelper()
    {
        return $this->datahelper;
    }

    public function postHelper()
    {
        return $this->posthelper;
    }
}
