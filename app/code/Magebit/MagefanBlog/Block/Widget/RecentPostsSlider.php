<?php
/**
 * This file is part of the Magebit_MagefanBlog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_MagefanBlog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\MagefanBlog\Block\Widget;

use Magefan\Blog\Model\ResourceModel\Post\Collection;
use Magefan\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Block class for recent posts slider widget
 */
class RecentPostsSlider extends Template implements BlockInterface
{
    private const SIZE_OF_BLOG_POSTS = 4;

    /**
     * @inheritDoc
     */
    protected $_template = "widget/recent-blog-posts-slider-widget.phtml";

    /**
     * @param Context               $context
     * @param CollectionFactory     $postCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        private readonly CollectionFactory $postCollectionFactory,
        private readonly StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns collection of recent blog posts
     *
     * @return Collection
     */
    public function getPostCollection(): Collection
    {
        return $this->postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setOrder('main_table.publish_time', 'desc')->setPageSize(self::SIZE_OF_BLOG_POSTS);
    }
}
