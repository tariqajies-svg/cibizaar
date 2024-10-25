<?php
/**
 * This file is part of the Magebit_MagedelightGa4 package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\MagedelightGa4\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class MagedelightGa4 implements ArgumentInterface
{
    /**
     * @param $html
     * @return string|null
     */
    public function getScriptTagContent($html): ?string
    {
        $match = [];
        preg_match('/<script>(.*?)<\/script>/si', $html, $match);
        return $match[1] ?: null;
    }
}
