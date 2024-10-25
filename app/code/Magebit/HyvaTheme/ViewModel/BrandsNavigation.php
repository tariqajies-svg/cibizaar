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

use CtiDigital\Configurator\ViewModel\Cms as CategoryHelper;
use Exception;
use Magebit\HyvaTheme\Service\BrandsNavigation as BrandsNavigationService;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class BrandsNavigation extends Navigation
{
    private ?Category $brandsCategory;

    /**
     * @param BrandsNavigationService $navigationService
     * @param CategoryHelper $helper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        private readonly BrandsNavigationService $navigationService,
        private readonly CategoryHelper $helper,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($navigationService, $helper, $storeManager, $scopeConfig);

        $this->brandsCategory = $this->helper->getCategory(
            'brands',
            (int)$this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return int|null
     */
    public function getBrandsCategoryId(): ?int
    {
        return $this->brandsCategory ? (int)$this->brandsCategory->getId() : null;
    }

    /**
     * @param $rootId
     * @param $maxLevel
     * @param $topLevel
     * @param array|null $brandCategories - array with all brands categories
     * @return array
     */
    public function getCustomNavigation($rootId, $maxLevel = 0, $topLevel = 0, array &$brandCategories = null): array
    {
        try {
            $menuTree = $this->navigationService->getCustomMenuTree($rootId, $maxLevel, $topLevel, $brandCategories);
        } catch (Exception $e) {
            return [];
        }

        return $this->getMenuData($menuTree);
    }
}
