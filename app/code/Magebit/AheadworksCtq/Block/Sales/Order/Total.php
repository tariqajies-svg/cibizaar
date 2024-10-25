<?php
/**
 * This file is part of the Magebit_AheadworksCtq package.
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
namespace Magebit\AheadworksCtq\Block\Sales\Order;

use Magento\Framework\DataObject\Factory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Total extends Template
{
    /**
     * @param Context $context
     * @param Factory $factory
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly Factory $factory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals(): static
    {
        $source = $this->getSource();
        if (!$source) {
            return $this;
        }

        if (!!(int)$source->getBaseAwCtqAmount()) {
            $this->getParentBlock()->addTotal(
                $this->factory->create(
                    [
                        'code'   => 'aw_ctq_amount',
                        'strong' => false,
                        'label'  => __('Negotiated Discount'),
                        'value'  => $source->getAwCtqAmount(),
                    ]
                )
            );
        }

        return $this;
    }

    /**
     * @return mixed
     */
    private function getSource()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            return $parentBlock->getSource();
        }

        return null;
    }
}
