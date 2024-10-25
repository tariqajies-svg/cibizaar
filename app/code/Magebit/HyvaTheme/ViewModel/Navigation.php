<?php
/**
 * This file is part of the Magebit_HyvaTheme package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\HyvaTheme\ViewModel;

use Hyva\Theme\ViewModel\Navigation as HyvaNavigation;
use CtiDigital\Configurator\ViewModel\Cms as CategoryHelper;
use Magebit\HyvaTheme\Service\Navigation as NavigationService;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Navigation extends HyvaNavigation
{
    private const XML_PATH_CATEGORY_URL_SUFFIX = 'catalog/seo/category_url_suffix';

    private ?Category $mainCategory;

    /**
     * @param NavigationService $navigationService
     * @param CategoryHelper $helper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        private readonly NavigationService $navigationService,
        private readonly CategoryHelper $helper,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($navigationService);
        $this->mainCategory = $this->helper->getCategory(
            'categories',
            (int) $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return int|null
     */
    public function getMainCategoryId(): ?int
    {
        return $this->mainCategory ? (int) $this->mainCategory->getId() : null;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getRootCategoryId(): int
    {
        return (int) $this->storeManager->getStore()->getRootCategoryId();
    }

    /**
     * @throws LocalizedException
     */
    public function getCustomNavigation($rootId, $maxLevel = 0, $topLevel = 0): array
    {
        $menuTree = $this->navigationService->getCustomMenuTree($rootId, $maxLevel, $topLevel);

        return $this->getMenuData($menuTree);
    }

    /**
     * @param array|int $rootId
     * @param int $maxLevel
     * @param int $topLevel
     * @return array
     * @throws LocalizedException
     */
    public function getCustomFooterNavigation(array|int $rootId, int $maxLevel = 0, int $topLevel = 0): array
    {
        $menuTree = $this->navigationService->getCustomFooterMenuTree($rootId, $maxLevel, $topLevel);

        return $this->getMenuData($menuTree);
    }

    /**
     * Get top level category list (for footer)
     *
     * @return array|false
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFooterNavigation(): array|false
    {
        $menuItems = $this->getCustomFooterNavigation([$this->getRootCategoryId(), $this->getMainCategoryId()], 5);

        if (!$menuItems) {
            return false;
        }

        if (count($menuItems) > 12) {
            array_splice($menuItems, 11);
            $menuItems['view-all'] = [
                'name' => __('View all categories'),
                'url' => $this->mainCategory->getUrl()
            ];
        }

        return $menuItems;
    }

    /**
     * Return const cache tag as there 200+ categories
     *
     * @return array|string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG];
    }

    /**
     * Retrieve Category Url suffix
     *
     * @return string
     */
    public function getCategoryUrlSuffix()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CATEGORY_URL_SUFFIX, ScopeInterface::SCOPE_STORE);
    }
}
