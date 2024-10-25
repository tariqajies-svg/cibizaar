<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaRequisitionLists\Plugin\Controller;

use Aheadworks\RequisitionLists\Api\Data\RequisitionListItemInterface;
use Magebit\HyvaRequisitionLists\Service\ConfigurableItem;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Configure implements HttpPostActionInterface
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

        $itemId = $this->request->getParam(RequisitionListItemInterface::ITEM_ID);
        $productId = $this->request->getParam(RequisitionListItemInterface::PRODUCT_ID);

        if (!$itemId) {
            $result = [
                'error' => __('Product list item key is required'),
            ];

            return $resultJson->setData($result);
        }

        return $resultJson->setData($this->configurableItem->execute($itemId, $productId));
    }
}
