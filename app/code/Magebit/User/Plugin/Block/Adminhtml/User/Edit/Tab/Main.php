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

declare(strict_types=1);

namespace Magebit\User\Plugin\Block\Adminhtml\User\Edit\Tab;

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\User\Block\User\Edit\Tab\Main as MainTab;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\UserFactory;

class Main
{
    /**
     * @param RequestInterface $request
     * @param UserResource $userResource
     * @param UserFactory $userFactory
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly UserResource $userResource,
        private readonly UserFactory $userFactory,
    ) {
    }

    /**
     * Concat additional fields to admin user edit form
     *
     * @param MainTab $subject
     * @param Closure $proceed
     * @return string
     */
    public function aroundGetFormHtml(MainTab $subject, Closure $proceed): string
    {
        $form = $subject->getForm();
        $form->setData('enctype', 'multipart/form-data');

        if (is_object($form)) {
            $fieldset = $form->addFieldset(
                'additional_info',
                ['legend' => __('Additional Information')]
            );

            $fieldset->addField(
                'telephone',
                'text',
                [
                    'name' => 'telephone',
                    'label' => __('Phone Number'),
                    'id' => 'telephone',
                    'title' => __('Phone Number'),
                    'required' => false,
                ]
            );

            $fieldset->addField(
                'image_loader',
                'image',
                [
                    'name' => 'image_loader',
                    'label' => __('Image'),
                    'title' => __('Image'),
                    'id' => 'profile-image',
                    'note' => __('Allowed jpg, jpeg, gif and png file types')
                ]
            );

            $user = $this->userFactory->create();
            $this->userResource->load($user, (int) $this->request->getParam('user_id'));
            $image = $user->getData('image');

            $form->addValues([
                'telephone' => $user->getData('telephone'),
                'image_loader' => $image ? 'manager/' . $user->getData('image') : ''
            ]);
            $subject->setForm($form);
        }

        return $proceed();
    }
}
