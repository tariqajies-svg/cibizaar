<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaAheadworksCa\Block\Company\Domain;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Ca\Ui\DataProvider\Company\Domain\ListingDataProviderFactory;
use Aheadworks\Ca\Ui\DataProvider\Company\Domain\ListingDataProvider;
use Zend_Json;

class Listing extends Template
{
    /** @var ListingDataProvider */
    private ListingDataProvider $dataProvider;

    /**
     * Listing constructor.
     *
     * @param Context                    $context
     * @param ListingDataProviderFactory $dataProviderFactory
     * @param array                      $data
     */
    public function __construct(
        Context                    $context,
        ListingDataProviderFactory $dataProviderFactory,
        array                      $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataProvider = $dataProviderFactory->create([
            'name' => 'aw_ca_company_domain_listing_data_source',
            'primaryFieldName' => 'id',
            'requestFieldName' => 'id',
        ]);
    }

    /**
     * Get Data from @see ListingDataProvider
     *
     * @return string
     */
    public function getDataProviderData(): string
    {
        return Zend_Json::encode($this->dataProvider->getData());
    }
}
