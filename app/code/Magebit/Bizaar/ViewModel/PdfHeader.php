<?php
/**
 * This file is part of the Magebit_Bizaar package.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 */

declare(strict_types=1);

namespace Magebit\Bizaar\ViewModel;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class PdfHeader implements ArgumentInterface
{
    public const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Filesystem $filesystem,
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly AddressConfig $addressConfig,
        private readonly Mapper $addressMapper
    ) {
    }

    /**
     * @param string $mediaPath
     * @param string $file
     * @return string
     */
    public function getMediaImage(string $mediaPath, string $file): string
    {
        $media = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        return $media->getAbsolutePath($mediaPath . $file);
    }

    /**
     * Retrieve logo for PDF headers - the same as used in emails, will need its own config in future
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getHeaderLogo(): string
    {
        $fileName = $this->scopeConfig->getValue(
            self::XML_PATH_DESIGN_EMAIL_LOGO,
            Scope::SCOPE_STORE
        );

        return $this->getMediaImage('/email/logo/', $fileName);
    }

    /**
     * @param $quote
     * @return string
     */
    public function getBillingAddress($quote): string
    {
        try {
            $address = $quote->getCart()->getBillingAddress();

            if (isset($address)) {
                $addressId = $address['customer_address_id'];
                $addressData = $this->addressRepository->getById($addressId);
                $this->addressConfig->setStore($quote->getStoreId());
                $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
                return $renderer->renderArray(
                    $this->addressMapper->toFlatArray($addressData)
                );
            }
        } catch (\Exception $e) {
            return '';
        }

        return '';
    }
}
