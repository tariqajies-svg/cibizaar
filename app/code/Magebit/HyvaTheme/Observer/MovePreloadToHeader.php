<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\Observer;

use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class MovePreloadToHeader implements ObserverInterface
{
    public const REGEX_PRELOAD = '/(<link(?:\s+[^>]*?)?rel="preload"(?:\s+[^>]*?)?>)/';

    /**
     * We need to move all preload tags into to the head.
     * This function does this by using regex.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var HttpResponse $response */
        $response = $observer->getEvent()->getData('response');
        $content = $response->getContent();

        if (preg_match_all(self::REGEX_PRELOAD, $content, $matches)) {
            $linkTags = implode("", $matches[0]);
            $newContent = preg_replace(self::REGEX_PRELOAD, '', $content);
            $newContent = preg_replace('/<\/head>/', $linkTags . '</head>', $newContent, 1);
            $response->setContent($newContent);
        }
    }
}
