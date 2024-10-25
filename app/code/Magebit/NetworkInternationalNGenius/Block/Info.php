<?php
/**
 * This file is part of the Magebit_NetworkInternationalNGenius package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\NetworkInternationalNGenius\Block;

use NetworkInternational\NGenius\Block\Info as InfoParent;

class Info extends InfoParent
{
    /**
     * @var string
     */
    protected $_template = 'Magebit_NetworkInternationalNGenius::payment/info.phtml';
}
