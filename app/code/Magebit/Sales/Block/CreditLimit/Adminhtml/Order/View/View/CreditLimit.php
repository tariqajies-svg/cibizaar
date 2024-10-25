<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);
namespace Magebit\Sales\Block\CreditLimit\Adminhtml\Order\View\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class CreditLimit extends DefaultRenderer
{
    public const CREDIT_LIMIT_PAYMENT_TITLE = 'aw_credit_limit';

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function showCreditLimit(): bool
    {
        return $this->getOrder()->getPayment()->getMethod() === self::CREDIT_LIMIT_PAYMENT_TITLE;
    }
}
