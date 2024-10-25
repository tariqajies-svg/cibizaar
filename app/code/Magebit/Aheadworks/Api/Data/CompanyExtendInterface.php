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

namespace Magebit\Aheadworks\Api\Data;

interface CompanyExtendInterface
{
    /**
     * String constants for property names
     */
    public const ID = "id";
    public const COMPANY_ID = "company_id";
    public const DESCRIPTION = "description";
    public const WEBSITE = "website";
    public const TLN = "tln";

    /**
     * Getter for CompanyId.
     *
     * @return int|null
     */
    public function getCompanyId(): ?int;

    /**
     * Setter for CompanyId.
     *
     * @param int|null $companyId
     *
     * @return void
     */
    public function setCompanyId(?int $companyId): void;

    /**
     * Getter for Description.
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Setter for Description.
     *
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void;

    /**
     * Getter for Website.
     *
     * @return string|null
     */
    public function getWebsite(): ?string;

    /**
     * Setter for Website.
     *
     * @param string|null $website
     *
     * @return void
     */
    public function setWebsite(?string $website): void;

    /**
     * Getter for Tln.
     *
     * @return string|null
     */
    public function getTln(): ?string;

    /**
     * Setter for Tln.
     *
     * @param string|null $tln
     *
     * @return void
     */
    public function setTln(?string $tln): void;
}
