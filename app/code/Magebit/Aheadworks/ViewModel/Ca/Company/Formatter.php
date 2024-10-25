<?php
/**
 * This file is part of the Magebit_Aheadworks package.
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

namespace Magebit\Aheadworks\ViewModel\Ca\Company;

use Aheadworks\Ca\Api\Data\CompanyInterface;
use Aheadworks\Ca\Model\Company\Address\Renderer as AddressRenderer;
use Aheadworks\Ca\Model\Magento\ModuleUser\UserRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class Formatter extends \Aheadworks\Ca\ViewModel\Company\Formatter
{
    /**
     * @param AddressRenderer $addressRenderer
     * @param UserRepository $userRepository
     */
    public function __construct(
        private readonly AddressRenderer $addressRenderer,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct(
            $this->addressRenderer,
            $this->userRepository
        );
    }

    /**
     * @param CompanyInterface $company
     * @return string|null
     */
    public function getSalesRepresentativeTelephone(CompanyInterface $company): ?string
    {
        try {
            $user = $this->userRepository->getById($company->getSalesRepresentativeId());
            $value = $user->getData('telephone');
        } catch (NoSuchEntityException $exception) {
            $value = '';
        }

        return $value;
    }

    /**
     * @param CompanyInterface $company
     * @return string|null
     */
    public function getSalesRepresentativeImage(CompanyInterface $company): ?string
    {
        try {
            $user = $this->userRepository->getById($company->getSalesRepresentativeId());
            $value = $user->getData('image') ? 'manager/' . $user->getData('image') : null;
        } catch (NoSuchEntityException $exception) {
            $value = '';
        }

        return $value;
    }
}
