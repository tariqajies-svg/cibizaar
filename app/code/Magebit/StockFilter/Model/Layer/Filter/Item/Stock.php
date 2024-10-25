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

namespace Magebit\StockFilter\Model\Layer\Filter\Item;

use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Framework\Exception\LocalizedException;

class Stock extends Item
{
    /**
     * @return string|NULL
     */
    public function getUrl(): ?string
    {
        $qsParams = $this->getApplyQueryStringParams();

        $url = $this->rewriteBaseUrl($qsParams);

        if ($url === null) {
            $url = $this->_url->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $qsParams]);
        }

        return $url;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getRemoveUrl(): string
    {
        $query = [$this->getFilter()->getRequestVar() => $this->getFilter()->getResetValue()];

        if ($this->getApplyValue() !== null) {
            $query = [$this->getFilter()->getRequestVar() => null];
        }

        $params = [
            '_current'     => true,
            '_use_rewrite' => true,
            '_query'       => $query,
            '_escape'      => true,
        ];

        return $this->_url->getUrl('*/*/*', $params);
    }

    /**
     * Append url and is_selected computed fields to the result array.
     *
     * @param array $keys
     * @return array
     */
    public function toArray(array $keys = []): array
    {
        $data = parent::toArray($keys);

        if (in_array('url', $keys) || empty($keys)) {
            $data['url'] = $this->getUrl();
        }

        if (in_array('is_selected', $keys) || empty($keys)) {
            $data['is_selected'] = (bool) $this->getIsSelected();
        }

        return $data;
    }

    /**
     * Return the value used to apply the filter.
     *
     * @return string|array|int
     */
    private function getApplyValue(): array|string|int
    {
        $value = $this->getValue();

        if (is_array($this->getApplyFilterValue())) {
            $value = $this->getApplyFilterValue();
        }

        if (is_array($value) && count($value) == 1) {
            $value = current($value);
        }

        return $value;
    }

    /**
     * Get query string params used to apply the filter.
     *
     * @return array
     * @throws LocalizedException
     */
    private function getApplyQueryStringParams(): array
    {
        $qsParams = [
            $this->getFilter()->getRequestVar() => $this->getApplyValue(),
            $this->_htmlPagerBlock->getPageVarName() => null,
        ];

        return $qsParams;
    }

    /**
     * Create the URL used to apply the filter from a existing URL.
     *
     * @param array $qsParams Query string params.
     *
     * @return NULL|string
     */
    private function rewriteBaseUrl(array $qsParams): ?string
    {
        $url = null;

        if ($this->getBaseUrl()) {
            $baseUrlParts = explode('?', $this->getBaseUrl());
            $qsParser     = new \Laminas\Stdlib\Parameters();

            $qsParser->fromArray($qsParams);

            if (count($baseUrlParts) > 1) {
                $qsParser->fromString($baseUrlParts[1]);
                $qsParams = array_merge($qsParser->toArray(), $qsParams);
                $qsParser->fromArray($qsParams);
            }

            $baseUrlParts[1] = $qsParser->toString();

            $url = implode('?', $baseUrlParts);
        }

        return $url;
    }
}
