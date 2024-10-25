<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaRequisitionLists\ViewModel\Modal;

use Hyva\Theme\Model\Modal\ModalBuilderFactory;
use Hyva\Theme\Model\Modal\ConfirmationBuilderFactory;
use Hyva\Theme\ViewModel\Modal;
use Magento\Framework\Escaper;
use Hyva\Theme\Model\Modal\ModalBuilderInterface;

class CreateNewListModal extends Modal
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param ModalBuilderFactory $modalBuilderFactory
     * @param ConfirmationBuilderFactory $confirmationBuilderFactory
     * @param Escaper $escaper
     */
    public function __construct(
        ModalBuilderFactory $modalBuilderFactory,
        ConfirmationBuilderFactory $confirmationBuilderFactory,
        Escaper $escaper
    ) {
        parent::__construct($modalBuilderFactory, $confirmationBuilderFactory);
        $this->escaper = $escaper;
    }

    /**
     * @param string|null $formAction
     * @param string|null $formKey
     * @param string|null $loading
     * @return ModalBuilderInterface
     */
    public function getModal(?string $formAction, ?string $formKey, ?string $loading): ModalBuilderInterface
    {
        return $this->createModal()->withContent(<<<END_OF_CONTENT
                <h3 class="requisition-list-modal-header">{$this->escaper->escapeHtml(__('Create Requisition List'))}</h3>
                <div class="requisition-list-modal-content py-7.25 lg:pt-6.5 lg:pb-8">
                    <form
                        class="requisition-list-new"
                        x-data="initFormActions()"
                        action="{$formAction}"
                        @submit="submitForm(event)"
                        method="post"
                        id="list-validate-detail"
                    >
                        <div class="list-name required">
                            <label class="label" for="name">{$this->escaper->escapeHtml(__('Requisition List Name'))}</label>
                            <input class="form-input invalid:error" type="text" id="name" name="name" x-ref="listName">
                            <span x-show="error" class="error">{$this->escaper->escapeHtml(__('Field is required'))}</span>
                        </div>
                        <div class="list-description mt-3.5 lg:mt-3">
                            <label class="label" for="description">{$this->escaper->escapeHtml(__('Description'))}</label>
                            <textarea class="form-input" type="text" id="description" name="description" rows="4"></textarea>
                        </div>
                        <input name="form_key" type="hidden" value="{$formKey}"/>
                        <button type="submit" class="save-button btn btn-primary w-full mt-4 lg:mt-6">
                            {$this->escaper->escapeHtml(__('Save'))}
                        </button>
                        {$loading}
                    </form>
                    <button type="button" @click="hide" class="cancel-button link text-center w-full mt-4 lg:mt-6">
                        {$this->escaper->escapeHtml(__('Cancel'))}
                    </button>
                </div>
                END_OF_CONTENT
        )->positionBottom()
            ->withAriaLabelledby('dashboard-top-info-block')
            ->addContainerClass('pop-up', 'rounded-none')
            ->addOverlayClass('backdrop')
            ->addDialogClass('pop-up-dialog')
            ->removeOverlayClass('bg-black')
            ->removeContainerClass('rounded-lg')
            ->removeDialogClass('rounded-lg', 'shadow-xl');
    }
}
