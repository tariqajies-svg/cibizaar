<?php
/**
 * This file is part of the Magebit_BankTransfer package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_BankTransfer
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\BankTransfer\Plugin;

use Magento\Email\Block\Adminhtml\Template\Edit\Form;

class EmailVariables
{
    /**
     * Add new email variable - Bank Transfer Block
     *
     * @param Form $subject
     * @param array $result
     * @return array
     */
    public function afterGetVariables(Form $subject, array $result): array
    {
        return [...$result, [
            'label' => __('Bank Transfer'),
            'value' => [[
                'label' => __('Bank Transfer Block'),
                'value' => <<<BLOCK_CONTENT
                {{block class="Magebit\BankTransfer\Block\Adminhtml\Order\CmsBlock"
                    order_id=\$order_id
                    name="bank-transfer-payment"
                    template_id=""
                    template="Magebit_BankTransfer::payment_cms_block.phtml"
                    area="frontend"}}
                BLOCK_CONTENT
            ]]
        ]];
    }
}
