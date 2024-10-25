<?php
/**
 * This file is part of the Magebit_HyvaAheadworksQuickOrder package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksQuickOrder
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksQuickOrder\Controller\QuickOrders;

use Aheadworks\QuickOrder\Api\Data\ProductListItemInterface;
use Magebit\HyvaAheadworksQuickOrder\Service\ConfigurableItem;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class ConfigureItem implements HttpPostActionInterface
{
    /**
     * ConfigureItem constructor
     *
     * @param ConfigurableItem $configurableItem
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly ConfigurableItem $configurableItem,
        private readonly ResultFactory $resultFactory,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Get data for configurable product options
     *
     * @return Json
     * @throws NoSuchEntityException
     */
    public function execute(): Json
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $itemKey = $this->request->getParam(ProductListItemInterface::ITEM_KEY);

        if (!$itemKey) {
            $result = [
                'error' => __('Product list item key is required'),
            ];

            return $resultJson->setData($result);
        }

        return $resultJson->setData($this->configurableItem->execute($itemKey));
    }
}
