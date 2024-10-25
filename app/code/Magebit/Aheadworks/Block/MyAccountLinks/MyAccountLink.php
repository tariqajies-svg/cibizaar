<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */
namespace Magebit\Aheadworks\Block\MyAccountLinks;

use Aheadworks\RequisitionLists\Api\CustomerManagementInterface;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Link\Current as LinkCurrent;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class MyAccountLink extends LinkCurrent implements SortLinkInterface
{
    /**
     * Search redundant /index and / in url
     */
    private const REGEX_INDEX_URL_PATTERN = '/(\/index|(\/))+($|\/$)/';

    private HttpContext $httpContext;
    private CustomerManagementInterface $customerManagement;

    /**
     * @param TemplateContext $context
     * @param DefaultPathInterface $defaultPath
     * @param HttpContext $httpContext
     * @param CustomerManagementInterface $customerManagement
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        DefaultPathInterface $defaultPath,
        HttpContext $httpContext,
        CustomerManagementInterface $customerManagement,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->httpContext = $httpContext;
        $this->customerManagement = $customerManagement;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    protected function _toHtml(): string
    {
        $isActive = $this->customerManagement->isActiveForCurrentWebsite(
            $this->_storeManager->getWebsite()->getId()
        );
        if (!$isActive) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return array|int|mixed|null
     */
    public function getSortOrder()
    {
        return $this->getData('sortOrder');
    }

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        $urlByPath = preg_replace(self::REGEX_INDEX_URL_PATTERN, '', $this->getUrl($this->getPath()));
        return $this->getCurrent() ||
            ($urlByPath == preg_replace(self::REGEX_INDEX_URL_PATTERN, '', $this->getUrl($this->getMca()))) ||
            (str_starts_with(
                preg_replace(self::REGEX_INDEX_URL_PATTERN, '', $this->getUrl($this->getMca())),
                $urlByPath
            )) ||
            $this->isCurrentCmsUrl($urlByPath);
    }

    /**
     * Check if link URL equivalent to URL of currently displayed CMS page
     *
     * @param string $urlByPath
     * @return bool
     */
    private function isCurrentCmsUrl(string $urlByPath): bool
    {
        return ($urlByPath == preg_replace(self::REGEX_INDEX_URL_PATTERN, '', $this->getCurrentUrl()));
    }

    /**
     * Get Current displayed page url
     *
     * @return string
     */
    private function getCurrentUrl(): string
    {
        return $this->getUrl('*/*/*', ['_current' => false, '_use_rewrite' => true]);
    }

    /**
     * Get current mca
     *
     * @return string
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    private function getMca(): string
    {
        $routeParts = [
            (string)$this->_request->getModuleName(),
            (string)$this->_request->getControllerName(),
            (string)$this->_request->getActionName(),
        ];

        $parts = [];
        $pathParts = explode('/', trim($this->_request->getPathInfo(), '/'));
        foreach ($routeParts as $key => $value) {
            if (isset($pathParts[$key]) && $pathParts[$key] === $value) {
                $parts[] = $value;
            }
        }
        return implode('/', $parts);
    }
}
