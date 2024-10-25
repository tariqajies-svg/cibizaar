<?php
/**
 * This file is part of the Magebit_OrderStatusColor package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_OrderStatusColor
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\OrderStatusColor\Module\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

class StatusColumn extends Select
{
    /** @var OrderStatusCollection  */
    private OrderStatusCollection $orderStatusCollection;

    /**
     * @param Context $context
     * @param OrderStatusCollection $orderStatusCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderStatusCollection $orderStatusCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): StatusColumn
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputId(string $value): StatusColumn
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function getSourceOptions(): array
    {
        return $this->orderStatusCollection->toOptionArray();
    }
}
