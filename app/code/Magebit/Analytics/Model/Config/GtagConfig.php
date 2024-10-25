<?php

/**
 * This file is part of the Magebit_Analytics package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Dunkin
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */
declare(strict_types=1);

namespace Magebit\Analytics\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class GtagConfig
{
    /**
     * Config paths for using throughout the code
     */
    private const XML_PATH_ACTIVE = 'google/gtag/analytics4/active';

    /**
     * Config paths for using throughout the code
     */
    private const XML_PATH_IS_LUMA = 'google/gtag/analytics4/is_luma';

    private const XML_PATH_MEASUREMENT_ID = 'google/gtag/analytics4/measurement_id';

    /**
     * Google Ads conversion src
     */
    private const GTAG_GLOBAL_SITE_TAG_SRC = 'https://www.googletagmanager.com/gtag/js?id=';

    /**#@+
     * Google AdWords config data
     */
    private const XML_PATH_ADWORD_ACTIVE = 'google/gtag/adwords/active';

    private const XML_PATH_CONVERSION_ID = 'google/gtag/adwords/conversion_id';

    private const XML_PATH_CONVERSION_LABEL = 'google/gtag/adwords/conversion_label';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->getMeasurementId();
    }

    /**
     * Get Measurement Id (GA4)
     *
     * @return string
     */
    public function getMeasurementId(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_MEASUREMENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Google Ads  active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADWORD_ACTIVE,
            ScopeInterface::SCOPE_STORE
        ) &&
            $this->getConversionId() &&
            $this->getConversionLabel();
    }

    /**
     * Is Google Ads  configurable
     *
     * @return bool
     */
    public function isGoogleAdwordsConfigurable(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADWORD_ACTIVE,
            ScopeInterface::SCOPE_STORE
        ) && $this->getConversionId();
    }

    /**
     * Get conversion js src
     *
     * @return string
     */
    public function getConversionGtagGlobalSiteTagSrc(): string
    {
        $siteSrc = self::GTAG_GLOBAL_SITE_TAG_SRC;
        $cId = $this->getConversionId();
        return $siteSrc . $cId;
    }

    /**
     * Get Google Ads  conversion id
     *
     * @return string
     */
    public function getConversionId(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google Ads  conversion label
     *
     * @return string
     */
    public function getConversionLabel(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isLuma(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_LUMA);
    }
}
