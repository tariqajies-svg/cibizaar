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

namespace Magebit\MagefanBlog\ViewModel;

use Magefan\Blog\Api\CommentRepositoryInterface;
use Magefan\Blog\Model\Comment as CommentModel;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Comment implements ArgumentInterface
{
    /**
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository
    ) {
    }

    /**
     * @param int $id
     * @return CommentModel|mixed
     */
    public function getComment(int $id): mixed
    {
        return $this->commentRepository->getById($id);
    }
}
