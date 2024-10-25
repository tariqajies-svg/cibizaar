<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
namespace Magebit\Sales\Block\CreditLimit\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class MultiDayConfig extends AbstractFieldArray
{
    /**
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('value', ['label' => __('Day'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
