<?php
/**
 * Magedelight
 *
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

namespace Magedelight\Ga4\Block\TagManager;

use Magedelight\Ga4\Block\TagManager\Manager;

class CatLandingPage extends Manager
{
    /**
     * GetProductCollection
     */
    public function getProductCollection()
    {
        $categoryProductListBlock = $this->_layout->getBlock('category.products.list');

        if (empty($categoryProductListBlock)) {
            return [];
        }

        $categoryProductListBlock->toHtml();
        $collection = $categoryProductListBlock->getLoadedProductCollection();
        $collection->setCurPage($this->getCurrentPage())->setPageSize($this->getLimit());

        return $collection;
    }

    /**
     * GetLimit
     */
    protected function getLimit()
    {
        $productListBlockToolbar = $this->_layout->getBlock('product_list_toolbar');
        if (empty($productListBlockToolbar)) {
            return 9;
        }

        return (int) $productListBlockToolbar->getLimit();
    }

    /**
     * GetCurrentPage
     */
    protected function getCurrentPage()
    {
        $page = (int) $this->_request->getParam('p');
        if (!$page) {
            return 1;
        }

        return $page;
    }
}
