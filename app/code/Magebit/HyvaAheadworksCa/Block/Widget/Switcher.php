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

namespace Magebit\HyvaAheadworksCa\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class Switcher extends Template implements BlockInterface
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->setTemplate('company/switcher.phtml');
        parent::__construct($context, $data);
    }
}
