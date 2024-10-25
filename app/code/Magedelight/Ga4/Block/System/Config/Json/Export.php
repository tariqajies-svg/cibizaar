<?php
/**
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

namespace Magedelight\Ga4\Block\System\Config\Json;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Export extends Field
{
    /**
     * @var $_template
     */
    protected $_template = 'Magedelight_Ga4::system/json/download_file.phtml';

    /**
     * @var $GetPathForJsonData
     */
    protected $getPathForJsonData = null;

    /**
     * @var $GetPathJsonSentData
     */
    protected $getPathJsonSentData = null;

    /**
     * Construct
     * phpcs:disable
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get form action URL.
     *
     * @param array $element
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * GetPathForJsonData.
     *
     * @return string
     */
    public function getPathForJsonData()
    {
        $this->jsonGenerationtPath = $this->_urlBuilder->getUrl('ga4/data/create');
        return $this->jsonGenerationtPath;
    }

    /**
     * GetPathJsonSentData.
     *
     * @return string
     */
    public function getPathJsonSentData()
    {
        $this->jsonExportPath = $this->_urlBuilder->getUrl('ga4/data/export');
        return $this->jsonExportPath;
    }
}
