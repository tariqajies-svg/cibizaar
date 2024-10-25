<?php
/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\Sales\Plugin\Payment\Helper;

use Exception;
use Magebit\Sales\Block\Email\Payment\PoNumber;
use Magento\Framework\App\Area;
use Magento\Payment\Helper\Data as DataSubject;
use Magento\Payment\Model\InfoInterface;
use Magento\Store\Model\App\Emulation;

class Data
{
    /**
     * @param Emulation $appEmulation
     * @param PoNumber $poNumberBlock
     */
    public function __construct(
        protected readonly Emulation $appEmulation,
        protected readonly PoNumber $poNumberBlock
    ) {
    }

    /**
     * Add PO number to payment info block
     *
     * @throws Exception
     */
    public function afterGetInfoBlockHtml(
        DataSubject $subject,
        string $result,
        InfoInterface $info,
        string|int $storeId
    ): string {
        if (!$info->getPoNumber()) {
            return $result;
        }

        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);

        try {
            $result .= $this->poNumberBlock
                ->setPoNumber($info->getPoNumber())
                ->setArea(Area::AREA_FRONTEND)
                ->toHtml();
        } catch (Exception $exception) {
            $this->appEmulation->stopEnvironmentEmulation();
            throw $exception;
        }

        $this->appEmulation->stopEnvironmentEmulation();

        return $result;
    }
}
