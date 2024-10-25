<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
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

namespace Magebit\HyvaTheme\ViewModel;

use Hyva\Theme\Model\Modal\ModalBuilderInterface;
use Hyva\Theme\ViewModel\Modal as HyvaModal;

class Modal extends HyvaModal
{
    /**
     * @return ModalBuilderInterface
     */
    public function getModal(): ModalBuilderInterface
    {
        return $this->createModal()
            ->positionBottom()
            ->addContainerClass('pop-up')
            ->addOverlayClass('backdrop')
            ->addDialogClass('pop-up-dialog')
            ->removeOverlayClass('bg-black')
            ->removeContainerClass('rounded-lg', 'items-center')
            ->removeDialogClass('rounded-lg', 'max-h-screen', 'shadow-xl', 'p-10');
    }
}
