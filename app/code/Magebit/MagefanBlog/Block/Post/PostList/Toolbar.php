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

namespace Magebit\MagefanBlog\Block\Post\PostList;

use Magefan\Blog\Block\Post\PostList\Toolbar as MagefanToolbar;

class Toolbar extends MagefanToolbar
{
    /**
     * Pager number of items from which products started on current page.
     *
     * @return float|int
     */
    public function getFirstNum(): float|int
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * Pager number of items products finished on current page.
     *
     * @return int
     */
    public function getLastNum(): float|int
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * Return last page number.
     *
     * @return int
     */
    public function getLastPageNum(): int
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Total number of products in current category.
     *
     * @return int
     */
    public function getTotalNum(): int
    {
        return $this->getCollection()->getSize();
    }
}
