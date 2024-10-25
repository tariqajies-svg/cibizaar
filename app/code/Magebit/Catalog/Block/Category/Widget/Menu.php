<?php
/**
 * This file is part of the Magebit_Catalog package.
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

namespace Magebit\Catalog\Block\Category\Widget;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Store\Model\StoreManagerInterface;

class Menu extends Template implements BlockInterface
{
    private Collection $collection;

    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        $this->setTemplate($this->getData('template_type'));

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getHeading(): string
    {
        return $this->getData('heading') ?? '';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getData('description') ?? '';
    }

    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getCategoryList(): Collection
    {

        $this->collection = $this->collectionFactory->create()
            ->addAttributeToSelect(['name', 'url', 'image'])
            ->addFieldToFilter(
                'entity_id',
                ['in' => explode(',', $this->getData('category_ids'))]
            )
            ->addPathFilter('^1\/' . $this->storeManager->getStore()->getRootCategoryId() . '\/.*')
            ->setOrder('position', 'ASC')
            ->load();

        return $this->collection;
    }

    /**
     * Get category collection cache identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $identities = [];
        foreach ($this->collection as $category) {
            $identities[] = $category->getIdentities();
        }

        return $identities;
    }
}
