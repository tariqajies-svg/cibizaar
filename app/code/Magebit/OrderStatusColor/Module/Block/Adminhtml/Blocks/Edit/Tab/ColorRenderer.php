<?php
/**
 * This file is part of the Magebit_OrderStatusColor package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_OrderStatusColor
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);
namespace Magebit\OrderStatusColor\Module\Block\Adminhtml\Blocks\Edit\Tab;

use Magento\Framework\View\Element\AbstractBlock;

class ColorRenderer extends AbstractBlock
{
    /**
     * @return string
     */
    public function _toHtml(): string
    {
        $html = '<input type="text" name="' . $this->getInputName() . '" id="' . $this->getInputId() . '" >';
        $html .= '<script type="text/javascript">
                require(["jquery","jquery/colorpicker/js/colorpicker"], function ($, colorPicker) {
                    $(document).ready(function () {
                        var $el = $("#'.$this->getInputId().'");
                        $el.css("background", $el.val());

                        // Attach the color picker
                        $el.ColorPicker({
                            onChange: function (hsb, hex, rgb) {
                                $el.css("backgroundColor", "#" + hex).val("#" + hex);
                            }
                        });
                    });
                });
                </script>';

        return $html;
    }
}
