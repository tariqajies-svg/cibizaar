<?php
/**
 * This file is part of the Magebit_MagefanBlog package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_MagefanBlog
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\MagefanBlog\Block;

use Magefan\Blog\Block\Index as MagefanIndex;
use Magefan\Blog\Model\Post;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends MagefanIndex
{

    /**
     * @var Post
     */
    protected $_highlightPost;

    /**
     * Prepare post collection excluding highlighted post
     *
     * @return void
     * @throws NoSuchEntityException
     */
    protected function _preparePostCollection(): void
    {
        $this->_prepareHighlightPost();

        $this->_postCollection = $this->_postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder($this->getCollectionOrderField(), $this->getCollectionOrderDirection());

        if ($this->_highlightPost) {
            $this->_postCollection->addFieldToFilter('post_id', ['neq' => $this->_highlightPost->getId()]);
        }

        if ($this->getPageSize()) {
            $this->_postCollection->setPageSize($this->getPageSize());
        }
    }

    /**
     * Prepare highlighted post
     *
     * @return void
     * @throws NoSuchEntityException
     */
    protected function _prepareHighlightPost(): void
    {
        $this->_highlightPost = $this->_postCollectionFactory->create()
            ->addActiveFilter()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder($this->getCollectionOrderField(), $this->getCollectionOrderDirection())
            ->getFirstItem();
    }

    /**
     * Get highlighted post
     *
     * @return Post
     * @throws NoSuchEntityException
     */
    public function getHighlightPost(): Post
    {
        if (null === $this->_highlightPost) {
            $this->_prepareHighlightPost();
        }

        return $this->_highlightPost;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        $page = (int) $this->_request->getParam($this->getPageParamName());
        return $page ?: 1;
    }
}
