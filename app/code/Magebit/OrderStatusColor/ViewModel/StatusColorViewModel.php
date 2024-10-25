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
namespace Magebit\OrderStatusColor\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class StatusColorViewModel implements ArgumentInterface
{
    public const STATUS_COLOR_CONFIG_PATH = 'sales/order_status_color/colors';
    private ?string $colorConfigs = null;

    /** @var ScopeConfigInterface  */
    private ScopeConfigInterface $scopeConfig;

    /** @var Serialize  */
    private Serialize $serialize;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Serialize $serialize
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serialize $serialize
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serialize = $serialize;
    }

    /**
     * @param $statusCode
     * @return ?string
     */
    public function getColor($statusCode): ?string
    {
        $configArray = $this->getColorConfigs();

        if (!$configArray) {
            return null;
        }

        foreach ($this->serialize->unserialize($configArray) as $item) {
            if ($item['status'] !== $statusCode) {
                continue;
            }

            return $item['color'];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getAllColors(): ?string
    {
        $configArray = $this->getColorConfigs();

        if (!$configArray) {
            return null;
        }

        return json_encode(array_column($this->serialize->unserialize($configArray), 'color', 'status'));
    }

    /**
     * @return ?string
     */
    public function getColorConfigs(): ?string
    {
        if (!$this->colorConfigs) {
            $this->colorConfigs = $this->scopeConfig->getValue(
                self::STATUS_COLOR_CONFIG_PATH,
                ScopeInterface::SCOPE_STORES
            );
        }

        return $this->colorConfigs;
    }
}
