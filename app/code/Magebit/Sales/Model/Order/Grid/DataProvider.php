<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\Sales\Model\Order\Grid;

use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as CoreDataProvider;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Sales order grid dataprovider class
 */
class DataProvider extends CoreDataProvider implements DataProviderInterface
{
    /**
     * Add field filter to collection
     *
     * @param Filter $filter
     *
     * @return mixed
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() === 'created_at') {
            $filter->setField('main_table.created_at');
        }

        $this->searchCriteriaBuilder->addFilter($filter);
    }
}
