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

namespace Magebit\HyvaAheadworksCa\Controller\Role;

use Aheadworks\Ca\Controller\Role\Edit as RoleEdit;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * CA Roles and permissions Edit page controller
 */
class Edit extends RoleEdit
{
    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute(): ResultInterface
    {
        $resultPage = parent::execute();
        $resultPage->getConfig()->getTitle()->set(__('Roles and Permissions'));

        return $resultPage;
    }
}
