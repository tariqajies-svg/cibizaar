<?php
/**
 * This file is part of the Magebit_PageBuilder package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\PageBuilder\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Slide extends Template implements BlockInterface
{
    /**
     * @param string $content
     * @return string
     */
    protected function decodeWysiwygCharacters(string $content): string
    {
        $wysiwygCharacters = ['/\^\[/', '/\^\]/', '/`/', '/\|/', '/&lt;/', '/&gt;/'];
        $normalizedCharacters = ['{', '}', '"', '\\', '<', '>'];

        $content = preg_replace($wysiwygCharacters, $normalizedCharacters, $content);
        return str_replace('\\"', '"', $content);
    }

    /**
     * @return array
     */
    public function getBackgroundImage(): array
    {
        return json_decode($this->decodeWysiwygCharacters($this->getData('background_image'))) ?: [];
    }

    /**
     * @return array
     */
    public function getMobileImage(): array
    {
        return json_decode($this->decodeWysiwygCharacters($this->getData('mobile_image'))) ?: [];
    }

    /**
     * @return array
     */
    public function getVideoFallbackImage(): array
    {
        return json_decode($this->decodeWysiwygCharacters($this->getData('video_fallback_image'))) ?: [];
    }
}
