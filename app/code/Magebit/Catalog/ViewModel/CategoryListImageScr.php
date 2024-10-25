<?php
/**
 * This file is part of the Magebit_Catalog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\Catalog\ViewModel;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryListImageScr implements ArgumentInterface
{
    /**
     * @var ImageBuilder
     * @since 102.0.0
     */
    protected ImageBuilder $imageBuilder;
    protected ?Product $product;
    protected ?array $imageDisplayAreas;
    protected ?array $sources = null;

    public function __construct(
        Context $context,
        protected readonly Escaper $escaper
    ) {
        $this->imageBuilder = $context->getImageBuilder();
    }

    /**
     * @return array
     */
    public function getSources(): array
    {
        if ($this->sources) {
            return $this->sources;
        }

        $sources = [];
        foreach ($this->imageDisplayAreas as $imageId) {
            $source = [
                'url' => $this->imageBuilder->create($this->product, $imageId)->getImageUrl()
            ];
            switch ($imageId) {
                case 'category_page_grid_small_mobile':
                    $source['media'] = '(max-width: 411px)';
                    $source['w'] = '148';
                    break;
                case 'category_page_grid_mobile':
                    $source['media'] = '(max-width: 767px)';
                    $source['w'] = '166';
                    break;
                case 'category_page_grid_tablet':
                    $source['media'] = '(max-width: 1023px)';
                    $source['w'] = '167';
                    break;
                case 'category_page_grid_large_tablet':
                    $source['media'] = '(max-width: 1279px)';
                    $source['w'] = '172';
                    break;
                case 'category_page_grid_small_desktop':
                    $source['media'] = '(max-width: 1535px)';
                    $source['w'] = '258';
                    break;
                case 'category_page_grid':
                    $source['media'] = '(min-width: 1536px)';
                    $source['w'] = '318';
                    break;
                default:
                    $source['media'] = '(min-width: 1px)';
                    $source['w'] = '318';
            }
            $sources[] = $source;
        }
        $this->sources = $sources;
        return $this->sources;
    }

    /**
     * @return Image|null
     */
    public function getInitialImage(): ?Image
    {
        if (isset($this->imageDisplayAreas[0])) {
            return $this->imageBuilder->create($this->product, $this->imageDisplayAreas[0]);
        }

        return null;
    }

    /**
     * @param Product $product
     * @param array $imageDisplayAreas
     * @return $this
     */
    public function init(Product $product, array $imageDisplayAreas): self
    {
        $this->product = $product;
        $this->imageDisplayAreas = $imageDisplayAreas;
        $this->sources = null;
        return $this;
    }

    /**
     * @return string
     */
    public function getSrcSet(): string
    {
        $srcSet = [];
        foreach ($this->getSources() as $source) {
            $srcSet[] = $this->escaper->escapeUrl($source['url']) . ' ' . $source['w'] . 'w';
        }
        return implode(',', $srcSet);
    }

    /**
     * @return string
     */
    public function getSizes(): string
    {
        $sizes = [];
        foreach ($this->getSources() as $source) {
            $sizes[] = $source['media'] . ' ' . $source['w'] . 'px';
        }
        return implode(',', $sizes);
    }
}
