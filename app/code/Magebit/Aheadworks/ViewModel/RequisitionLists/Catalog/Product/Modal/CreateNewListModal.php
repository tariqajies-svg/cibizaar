<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\Aheadworks\ViewModel\RequisitionLists\Catalog\Product\Modal;

use Hyva\Theme\Model\Modal\ConfirmationBuilderFactory;
use Hyva\Theme\Model\Modal\ModalBuilderFactory;
use Hyva\Theme\Model\Modal\ModalBuilderInterface;
use Magento\Framework\Escaper;
use Magebit\HyvaRequisitionLists\ViewModel\Modal\CreateNewListModal as Modal;

class CreateNewListModal extends Modal
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    public function __construct(
        ModalBuilderFactory $modalBuilderFactory,
        ConfirmationBuilderFactory $confirmationBuilderFactory,
        Escaper $escaper
    ) {
        parent::__construct($modalBuilderFactory, $confirmationBuilderFactory, $escaper);
        $this->escaper = $escaper;
    }

    /**
     * @param string|null $formAction
     * @param string|null $formKey
     * @param string|null $loading
     * @param string|null $dialogName
     * @return ModalBuilderInterface
     */
    public function getModal(?string $formAction, ?string $formKey, ?string $loading, ?string $dialogName = 'dialog'): ModalBuilderInterface
    {
        return $this->createModal()
            ->withDialogRefName($dialogName)
            ->withContent(
                <<<END_OF_CONTENT
                <h3 class="requisition-list-modal-header">{$this->escaper->escapeHtml(__('Create Requisition List'))}</h3>
                <div class="requisition-list-modal-content py-7.25 lg:pt-6.5 lg:pb-8">
                    <div
                        class="requisition-list-new"
                        id="list-validate-detail"
                    >
                        <div class="list-name field required">
                            <label class="label" for="name">{$this->escaper->escapeHtml(__('Requisition List Name'))}</label>
                            <input class="form-input" :class="{'error': error}" type="text" name="name" x-ref="listName">
                            <span x-show="error" class="block text-2xs lg:text-xs text-error font-bold">{$this->escaper->escapeHtml(__('Field is required'))}</span>
                        </div>
                        <div class="list-description mt-3.5 lg:mt-3">
                            <label class="label" for="description">{$this->escaper->escapeHtml(__('Description'))}</label>
                            <textarea class="form-input" x-ref="description" type="text" name="description" rows="4"></textarea>
                        </div>
                        <input x-ref="formKey" name="form_key" type="hidden" value="{$formKey}"/>
                        <button type="submit" @click.prevent="submitRequisitionList()" class="save-button btn btn-primary w-full mt-4 lg:mt-6">
                            {$this->escaper->escapeHtml(__('Save'))}
                        </button>
                        {$loading}
                    </div>
                    <button @click="hide('requisition-list-modal')" class="cancel-button link text-center w-full mt-4 lg:mt-6">
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
