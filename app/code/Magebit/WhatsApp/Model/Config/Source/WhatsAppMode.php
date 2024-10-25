<?php
/**
 * This file is part of the Magebit_WhatsApp package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_WhatsApp
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\WhatsApp\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WhatsAppMode implements OptionSourceInterface
{
    public const TEST = 'test';
    public const PROD = 'prod';

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::TEST,
                'label' => __('Test')
            ],
            [
                'value' => self::PROD,
                'label' => __('Production')
            ]
        ];
    }
}
