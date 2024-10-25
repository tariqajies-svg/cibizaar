<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Aheadworks
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Aheadworks\Plugin;

use Aheadworks\QuickOrder\Controller\Index\Index;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\Page as ResultPage;

class QuickOrderControllerIndexIndex
{
    public function __construct(
        protected readonly Config $pageConfig
    ) {
    }

    /**
     * Adding NOINDEX,NOFOLLOW for quick order page
     *
     * @param Index $subject
     * @param ResultPage $result
     * @return ResultPage
     */
    public function afterExecute(Index $subject, ResultPage $result): ResultPage
    {
        $this->pageConfig->setRobots('NOINDEX,NOFOLLOW');
        return $result;
    }
}
