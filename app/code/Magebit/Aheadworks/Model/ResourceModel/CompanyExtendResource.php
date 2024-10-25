<?php
/**
 * This file is part of the Magebit_Aheadworks package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Aheadworks
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Aheadworks\Model\ResourceModel;

use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CompanyExtendResource extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'aw_ca_company_extend_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('aw_ca_company_extend', CompanyExtendInterface::ID);
        $this->_useIsObjectNew = true;
    }
}
