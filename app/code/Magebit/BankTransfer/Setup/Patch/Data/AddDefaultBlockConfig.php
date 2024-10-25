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

namespace Magebit\BankTransfer\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDefaultBlockConfig implements DataPatchInterface
{
    private const BLOCK_IDENTIFIER = 'bank-transfer-block';
    private const BLOCK_CONFIG_PATH = 'payment/banktransfer/cmsblock';

    /**
     * AddDefaultBlockConfig constructor
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param WriterInterface $writer
     */
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly WriterInterface $writer
    ) {
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Add default block to config setting
     *
     * @return void
     * @throws LocalizedException
     */
    public function apply(): void
    {
        /** @var ?BlockInterface $block */
        $block = current($this->blockRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(BlockInterface::IDENTIFIER, self::BLOCK_IDENTIFIER)
                ->create()
        )->getItems());

        if ($block) {
            $this->writer->save(self::BLOCK_CONFIG_PATH, $block->getId());
        }
    }
}
