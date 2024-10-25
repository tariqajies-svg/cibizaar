<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
declare(strict_types=1);

namespace Magebit\HyvaRequisitionLists\Plugin;

use Magento\Framework\App\PageCache\Version;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Aheadworks\RequisitionLists\Controller\RList\AbstractRequisitionListAction;
use Magento\Framework\Controller\AbstractResult;

class LocalStoragePlugin extends Version
{
    /**
     * @param AbstractRequisitionListAction $subject
     * @param AbstractResult $result
     * @return AbstractResult
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function afterExecute(AbstractRequisitionListAction $subject, AbstractResult $result): AbstractResult
    {
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration(Version::COOKIE_PERIOD)
            ->setPath('/')
            ->setSecure($this->request->isSecure())
            ->setHttpOnly(false);
        $this->cookieManager->setPublicCookie(Version::COOKIE_NAME, $this->generateValue(), $publicCookieMetadata);

        return $result;
    }
}
