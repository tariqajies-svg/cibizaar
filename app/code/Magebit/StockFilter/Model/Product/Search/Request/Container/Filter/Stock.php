<?php
/**
 * This file is part of the Magebit_StockFilter package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_StockFilter
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\StockFilter\Model\Product\Search\Request\Container\Filter;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Smile\ElasticsuiteCore\Api\Search\Request\Container\FilterInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

class Stock implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly QueryFactory $queryFactory
    ) {
    }

    /**
     * Apply set filter on query
     *
     * @return QueryInterface|null
     */
    public function getFilterQuery(): ?QueryInterface
    {
        $query = null;
        $paramName = $this->scopeConfig->getValue('stockfilter/settings/url_param');
        $requestVal = $this->request->getParam($paramName);

        if ($requestVal !== null) {
            $query = $this->queryFactory->create(
                QueryInterface::TYPE_TERM,
                [
                    'field' => 'stock.is_in_stock',
                    'value' => (bool) $requestVal
                ]
            );
        }

        return $query;
    }
}
