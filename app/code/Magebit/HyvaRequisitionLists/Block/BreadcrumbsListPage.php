<?php
/**
 * This file is part of the Magebit_HyvaRequisitionLists package.
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

namespace Magebit\HyvaRequisitionLists\Block;

use Magento\Theme\Block\Html\Breadcrumbs;

/**
 * Adding current requisition list name to the breadcrumbs
 */
class BreadcrumbsListPage extends Breadcrumbs
{
    /**
     * @param $crumbName
     * @param $crumbInfo
     * @return $this
     */
    public function addCrumbWithTitle($crumbName, $crumbInfo)
    {
        parent::addCrumb($crumbName, $crumbInfo);

        if (isset($this->_crumbs[$crumbName]) && !$this->_crumbs[$crumbName]['readonly']) {
            $name = $this->getData('requisition_list_view_model')->getCurrentRequisitionListName();
            $this->_crumbs[$crumbName]['title'] = $name;
            $this->_crumbs[$crumbName]['label'] = $name;
        }

        return $this;
    }
}
