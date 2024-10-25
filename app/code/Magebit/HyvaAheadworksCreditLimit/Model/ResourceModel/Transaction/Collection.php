<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCreditLimit package.
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
namespace Magebit\HyvaAheadworksCreditLimit\Model\ResourceModel\Transaction;

use Aheadworks\CreditLimit\Model\ResourceModel\Transaction as ResourceTransaction;
use Aheadworks\CreditLimit\Model\ResourceModel\Transaction\Grid\Collection as DefaultCollection;
use Magento\Framework\Api\Search\AggregationInterface;

class Collection extends DefaultCollection
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @return AggregationInterface
     */
    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    /**
     * @param $aggregations
     * @return void
     */
    public function setAggregations($aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @return $this|Collection|DefaultCollection
     */
    protected function _initSelect(): DefaultCollection|Collection|static
    {
        $this->addFilterToMap('customer_id', 'aw_cl_summary_table.customer_id');

        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['awcte' => $this->getTable(ResourceTransaction::TRANSACTION_ENTITY_TABLE)],
                'main_table.id = awcte.transaction_id AND awcte.entity_type = \'order_id\'',
                ['entity_id']
            )->joinLeft(
                ['so' => $this->getTable('sales_order')],
                'awcte.entity_label = so.increment_id',
                ['credit_due_date']
            );

        return $this;
    }
}
