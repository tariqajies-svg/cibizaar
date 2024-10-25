<?php
/**
 * This file is part of the Magebit_TwoFa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_TwoFa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\TwoFa\ViewModel;

use Magebit\TwoFa\Service\TwoFaService;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class TwoFa implements ArgumentInterface
{
    /**
     * @param TwoFaService $twoFaService
     * @param LayoutInterface $layout
     */
    public function __construct(
        private readonly TwoFaService $twoFaService,
        private readonly LayoutInterface $layout
    ) {
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        $methods = [];

        foreach ($this->twoFaService->getAllMethods() as $method) {
            $methods[] = [
                'label' => $method->getLabel(),
                'value' => $method::CODE,
                'expiresIn' => $method->getExpiresIn()
            ];
        }

        return $methods;
    }

    /**
     * @return BlockInterface
     */
    public function getLoaderBlock(): BlockInterface
    {
        return $this->layout->createBlock(Template::class, 'loader-block-' . uniqid(), [
            'data' => [
                'template' => 'Hyva_Theme::ui/loading-block.phtml'
            ]
        ]);
    }
}
