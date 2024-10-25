<?php
/**
 * This file is part of the Magebit_HyvaAheadworksCa package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_HyvaAheadworksCa
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaAheadworksCa\ViewModel;

use Aheadworks\Ca\Model\Service\AuthorizationService;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AuthManagement extends AuthorizationService implements ArgumentInterface
{

}
