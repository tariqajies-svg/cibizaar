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

namespace Magebit\BankTransfer\Model\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CmsBlock implements OptionSourceInterface
{
    /**
     * @var array|null
     */
    private ?array $options = null;

    /**
     * CmsBlock constructor
     *
     * @param CollectionFactory $blockCollectionFactory
     */
    public function __construct(
        private readonly CollectionFactory $blockCollectionFactory
    ) {
    }

    /**
     * Get CMS Blocks
     *
     * @return array|null
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = $this->blockCollectionFactory->create()->toOptionArray();
            array_unshift(
                $options,
                [
                    'value' => '',
                    'label' => __('Please select a static block')
                ]
            );

            $this->options = $options;
        }

        return $this->options;
    }
}
