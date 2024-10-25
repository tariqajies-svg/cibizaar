<?php
/**
 * This file is part of the Magebit_User package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\User\Block\User\Edit;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Framework\Exception\LocalizedException;

class Form extends GenericForm
{
    /**
     * Extend form enctype => multipart/form-data to allow file upload
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareForm(): static
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]
        ]);
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
