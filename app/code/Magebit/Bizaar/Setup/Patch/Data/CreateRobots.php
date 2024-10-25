<?php
/**
 * This file is part of the Magebit_Bizaar package.
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

namespace Magebit\Bizaar\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Configure robots.txt
 */
class CreateRobots implements DataPatchInterface
{
    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        private ConfigInterface $config,
    ) {
    }

    /**
     * Run code inside patch If code fails
     */
    public function apply()
    {
        $this->config->saveConfig('design/search_engine_robots/default_robots', 'INDEX,FOLLOW');
        $this->config->saveConfig(
            'design/search_engine_robots/custom_instructions',
            'User-agent: *
Crawl-delay: 30
Disallow: /index.php/
Disallow: /*?
Disallow: /app/
Disallow: /lib/
Disallow: /*.php$
Disallow: /pkginfo/
Disallow: /report/
Disallow: /var/
Disallow: /catalog/
Disallow: /sendfriend/
Disallow: /review/
Disallow: /*SID=

Disallow: /privacy-policy
Disallow: /terms-conditions
Disallow: /distance-agreement
Disallow: /admin

# Restrict Checkout, Cart and user pages
Disallow: /checkout/
Disallow: /customer/
Disallow: /sales/
Disallow: /ccavenue/
Disallow: /newsletter/

Sitemap: https://www.bizaar.ai/sitemap.xml'
        );
    }

    /**
     * Inherit the long description from the parent class.
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Inherit the long description from the parent class.
     */
    public function getAliases()
    {
        return [];
    }
}
