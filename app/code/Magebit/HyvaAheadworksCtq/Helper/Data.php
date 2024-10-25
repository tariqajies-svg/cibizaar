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

namespace Magebit\HyvaAheadworksCtq\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const TOGGLE_REQUEST_QUOTE_MODAL_EVENT = 'toggle-request-quote-modal';
    public const IS_OPENED_QUOTE_MODAL_EVENT = 'is-opened-request-quote-modal';

    protected const XML_PATH_INFO_TOOLTIP_MESSAGE = 'aw_ctq/general/info_tooltip_message';

    /**
     * @return string|null
     */
    public function getQuoteInfoTooltipMessage(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INFO_TOOLTIP_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
