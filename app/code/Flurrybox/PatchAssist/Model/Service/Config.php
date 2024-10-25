<?php
/**
 * This file is part of the Flurrybox PatchAssist package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Flurrybox PatchAssist
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Flurrybox, Ltd. (https://flurrybox.com/)
 * @license   GNU General Public License ("GPL") v3.0
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Flurrybox\PatchAssist\Model\Service;

use Flurrybox\PatchAssist\Model\ServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Service for config actions
 *
 * @api
 */
class Config implements ServiceInterface
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResource;

    /**
     * Config constructor.
     *
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     */
    public function __construct(\Magento\Config\Model\ResourceModel\Config $configResource)
    {
        $this->configResource = $configResource;
    }

    /**
     * @param string $path
     * @param string|null $value
     * @param string $website
     * @param int $store
     *
     * @return Config
     * @throws LocalizedException
     */
    public function setConfig(
        string $path,
        ?string $value,
        string $website = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $store = 0
    ): Config {
        $parts = explode('/', $path);

        if (count($parts) < 3) {
            throw new LocalizedException(new Phrase('Incorrect config path provided'));
        }

        $this->configResource->saveConfig($path, $value, $website, $store);

        return $this;
    }

    /**
     * Delete config by its path.
     *
     * @param string $path
     * @param string $website
     * @param int $store
     *
     * @return Config
     *
     * @throws LocalizedException
     */
    public function deleteConfig(
        string $path,
        string $website = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $store = 0
    ): Config {
        $count = explode('/', $path);

        if (count($count) < 3) {
            throw new LocalizedException(new Phrase('Incorrect config path provided'));
        }

        $this->configResource->deleteConfig(rtrim($path, '/'), $website, $store);

        return $this;
    }
}
