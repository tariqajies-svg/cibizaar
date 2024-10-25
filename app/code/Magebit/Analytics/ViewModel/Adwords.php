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

namespace Magebit\Analytics\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magebit\Analytics\Model\Config\GtagConfig;

class Adwords implements ArgumentInterface
{
    /**
     * @var GtagConfig
     */
    private $gtagConfig;

    /**
     * @param GtagConfig $gtagConfig
     */
    public function __construct(GtagConfig $gtagConfig)
    {
        $this->gtagConfig = $gtagConfig;
    }

    /**
     * Is Google AdWords active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive(): bool
    {
        return $this->gtagConfig->isGoogleAdwordsActive();
    }

    /**
     * Is Google AdWords congifurable
     *
     * @return bool
     */
    public function isGoogleAdwordsConfigurable(): bool
    {
        return $this->gtagConfig->isGoogleAdwordsConfigurable();
    }

    /**
     * Get Google AdWords conversion id
     *
     * @return string
     */
    public function getConversionId(): string
    {
        return $this->gtagConfig->getConversionId();
    }

    /**
     * Get Google AdWords conversion label
     *
     * @return string
     */
    public function getConversionLabel(): string
    {
        return $this->gtagConfig->getConversionLabel();
    }

    /**
     * Get conversion js src
     *
     * @return string
     */
    public function getConversionGtagGlobalSiteTagSrc(): string
    {
        return $this->gtagConfig->getConversionGtagGlobalSiteTagSrc();
    }
}
