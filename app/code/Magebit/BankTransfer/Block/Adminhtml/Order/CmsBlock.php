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

namespace Magebit\BankTransfer\Block\Adminhtml\Order;

use Exception;
use Magento\Cms\Block\BlockByIdentifier;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\OfflinePayments\Model\Banktransfer;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class CmsBlock extends Template
{
    private const BLOCK_CONFIG_PATH = 'payment/banktransfer/cmsblock';

    /**
     * CmsBlock constructor
     *
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param BlockRepository $blockRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly BlockRepository $blockRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Check if payment method is bank transfer for email
     *
     * @param string|null $orderId
     * @return bool
     */
    public function isAllowed(?string $orderId): bool
    {
        if ($orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
                if ($order->getPayment()->getMethod() === Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE) {
                    return true;
                }
            } catch (NoSuchEntityException) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get CMS block that's assigned for bank transfer
     *
     * @return string
     */
    public function getCmsBlockContent(): string
    {
        try {
            $block =  $this->blockRepository->getById(
                $this->scopeConfig->getValue(self::BLOCK_CONFIG_PATH, ScopeInterface::SCOPE_STORE, $this->_storeManager->getStore()->getId())
            );

            return $this->getLayout()->createBlock(BlockByIdentifier::class)
                ->setIdentifier($block->getIdentifier())
                ->toHtml();
        } catch (Exception) {
            return '';
        }
    }
}
