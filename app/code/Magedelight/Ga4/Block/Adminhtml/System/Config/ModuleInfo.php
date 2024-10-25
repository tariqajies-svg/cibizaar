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

namespace Magedelight\Ga4\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Context;
use Magento\Framework\Module\PackageInfoFactory;

class ModuleInfo extends \Magento\Config\Block\System\Config\Form\Field\Heading
{

    /**
     * Render element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $label = $element->getLabel();

        $info = "<p>If you are upgrading to version 1.0.9 or newer from a previous version, in order to keep collecting data, regenerate and reimport the json file into your Google Tag Manager container, please choose Merge + Overwrite Option when promted. This is required because various dataLayer properties have been restructured.</p>";
        $label .= $info;
        $label .= '<p>If you are using fresh installation, follow the normal step which mentioned in our <a href="https://docs.magedelight.com/display/MAG/GA4+with+GTM" target="_blank">User guide</a>.</p>';

        return sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div id="%s" class="message message-info">%s</div></td></tr>',
            $element->getHtmlId(),
            $element->getHtmlId(),
            $label
        );
    }
}