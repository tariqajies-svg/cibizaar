<?php
/**
 * This file is part of the Magebit_User package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\User\Plugin\Controller\Adminhtml\User;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\User\Controller\Adminhtml\User\Save as SaveUserAction;

class Save
{
    /**
     * @param UploaderFactory $fileUploaderFactory
     * @param Filesystem $filesystem
     * @param MessageManagerInterface $messageManager
     * @param File $file
     */
    public function __construct(
        private readonly UploaderFactory $fileUploaderFactory,
        private readonly Filesystem $filesystem,
        private readonly MessageManagerInterface $messageManager,
        private readonly File $file
    ) {
    }

    /**
     * Save posted image before execute
     *
     * @param SaveUserAction $subject
     * @return SaveUserAction|void
     * @throws FileSystemException
     */
    public function beforeExecute(SaveUserAction $subject)
    {
        $request = $subject->getRequest();
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        if ($request->getFiles('image_loader')['size']) {
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'image_loader']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            try {
                $result = $uploader->save($mediaDir->getAbsolutePath('manager'));
                $request->setPostValue('image', $result['file']);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }

            return $subject;
        }

        if (isset($request->getPostValue('image_loader')['delete'])) {
            $fileName = $request->getPostValue('image_loader')['value'];
            if ($this->file->isExists($mediaDir->getAbsolutePath() . $fileName)) {
                $this->file->deleteFile($mediaDir->getAbsolutePath() . $fileName);
                $request->setPostValue('image', null);
            }
        }
    }
}
