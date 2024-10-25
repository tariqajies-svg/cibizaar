<?php
/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    public $urlBuilder;

    public $layout;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\View\LayoutInterface $layout,
        array $components = [],
        array $data = []
    ) {
           $this->urlBuilder = $urlBuilder;
          $this->layout = $layout;
           parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function getViewUrl()
    {
        return $this->urlBuilder->getUrl(
            $this->getData('config/viewUrlPath')
        );
    }
  
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = $this->layout->createBlock(
                        \Magento\Backend\Block\Widget\Button::class,
                        '',
                        [
                            'data' => [
                                'label' => __('View'),
                                'type' => 'button',
                                'disabled' => false,
                                'class' => 'ga4-grid-view detail-view-'.$item['entity_id'],
                                'onclick' => 'ga4View.open(\''. $this->getViewUrl().'\', \''.$item['entity_id'].'\')',
                            ]
                        ]
                    )->toHtml();
                }
            }
        }
        return $dataSource;
    }
}
