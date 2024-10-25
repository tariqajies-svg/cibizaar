<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\ViewModel\Payment;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use NetworkInternational\NGenius\Model\Core;
use NetworkInternational\NGenius\Model\ResourceModel\Core\CollectionFactory;

class Info implements ArgumentInterface
{
    public function __construct(
        protected readonly CollectionFactory $coreCollectionFactory
    ) {
    }

    /**
     * @param Order $order
     * @return Core
     */
    public function getNgeniusPaymentDetails(Order $order): Core
    {
        $collection = $this->coreCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getIncrementId());

        return $collection->getFirstItem();
    }
}
