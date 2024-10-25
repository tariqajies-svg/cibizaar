<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCtq package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCreditLimit
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCtq\ViewModel;

use Magebit\HyvaAheadworksCtq\Helper\Data as Helper;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class TooltipText extends DataObject implements ArgumentInterface
{
    public function __construct(
        protected readonly Helper $helper,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string|null
     */
    public function getQuoteInfoTooltipMessage(): ?string
    {
        return $this->helper->getQuoteInfoTooltipMessage();
    }
}
