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

namespace Magebit\Aheadworks\Model;

use Magebit\Aheadworks\Api\Data\CompanyExtendInterface;
use Magebit\LowStock\Model\ResourceModel\LowStockNotificationResource;
use Magento\Framework\Model\AbstractModel;

class CompanyExtendModel extends AbstractModel implements CompanyExtendInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'magebit_aw_ca_company';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(LowStockNotificationResource::class);
    }

    /**
     * Getter for CompanyId.
     *
     * @return int|null
     */
    public function getCompanyId(): ?int
    {
        return $this->getData(self::COMPANY_ID) === null ? null
            : (int)$this->getData(self::COMPANY_ID);
    }

    /**
     * Setter for CompanyId.
     *
     * @param int|null $companyId
     *
     * @return void
     */
    public function setCompanyId(?int $companyId): void
    {
        $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * Getter for Description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Setter for Description.
     *
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Getter for Website.
     *
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->getData(self::WEBSITE);
    }

    /**
     * Setter for Website.
     *
     * @param string|null $website
     *
     * @return void
     */
    public function setWebsite(?string $website): void
    {
        $this->setData(self::WEBSITE, $website);
    }

    /**
     * Getter for Tln.
     *
     * @return string|null
     */
    public function getTln(): ?string
    {
        return $this->getData(self::TLN);
    }

    /**
     * Setter for Tln.
     *
     * @param string|null $tln
     *
     * @return void
     */
    public function setTln(?string $tln): void
    {
        $this->setData(self::TLN, $tln);
    }
}
