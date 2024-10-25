<?php
/**
 * This file is part of the Magebit_Contact package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Contact
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Contact\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ContactOptions extends AbstractFieldArray
{
    protected function _prepareToRender(): void
    {
        $this->addColumn('name', [
            'label' => __('Subject Name'),
            'class' => 'required-entry'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
