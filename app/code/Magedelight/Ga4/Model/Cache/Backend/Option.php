<?php
/**
 *
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Model\Cache\Backend;

class Option extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    public const MD_CAT = 'md_ga4_categories';

    public const MD_TAG = 'MD_GA4_CATEGORIES';

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::MD_CAT), self::MD_TAG);
    }
}
