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

namespace Flurrybox\PatchAssist\Model;

/**
 * Patch service registry
 */
class ServiceRegistry
{
    /**
     * @var array
     */
    protected $services;

    /**
     * ServiceRegistry constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->services = $services;
    }

    /**
     * Get service objects
     *
     * @return ServiceInterface[]
     */
    public function getServices(): array
    {
        foreach ($this->services as $idx => $service) {
            if (!$service instanceof ServiceInterface) {
                unset($this->services[$idx]);
            }
        }

        return $this->services;
    }
}
