<?php
/**
 * This file is part of the Magebit_JivoChat package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_JivoChat
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit<info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\JivoChat\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;

class JivoChat extends Template
{
    private const JIVO_IS_ENABLED = 'jivochat/general/enable';

    private const JIVO_SOURCE = 'jivochat/general/source';

    /**
     * @return string
     */
    public function isEnabled(): string
    {
        return $this->_scopeConfig->getValue(self::JIVO_IS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getJivoSource(): string
    {
        return $this->_scopeConfig->getValue(self::JIVO_SOURCE, ScopeInterface::SCOPE_STORE);
    }
}
