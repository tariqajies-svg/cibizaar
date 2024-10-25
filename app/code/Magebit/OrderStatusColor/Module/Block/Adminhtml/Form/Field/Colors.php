<?php
/**
 * This file is part of the Magebit_OrderStatusColor package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_OrderStatusColor
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */
declare(strict_types=1);

namespace Magebit\OrderStatusColor\Module\Block\Adminhtml\Form\Field;

use Magebit\OrderStatusColor\Module\Block\Adminhtml\Blocks\Edit\Tab\ColorRenderer;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Colors extends AbstractFieldArray
{
    /** @var StatusColumn */
    private StatusColumn $statusColumn;

    /** @var ColorRenderer */
    private ColorRenderer $colorRenderer;

    /**
     * @param Context $context
     * @param StatusColumn $statusColumn
     * @param ColorRenderer $colorRenderer
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param array $data
     */
    public function __construct(
        Context $context,
        StatusColumn $statusColumn,
        ColorRenderer $colorRenderer,
        ?SecureHtmlRenderer $secureRenderer = null,
        array $data = []
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->statusColumn = $statusColumn;
        $this->colorRenderer = $colorRenderer;
    }

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('status', [
            'label' => __('Order Status'),
            'renderer' => $this->getStatusRenderer()
        ]);

        $this->addColumn('color', [
            'label' => __('Color'),
            'renderer' => $this->getColorRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getStatusRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return StatusColumn
     * @throws LocalizedException
     */
    private function getStatusRenderer(): StatusColumn
    {
        if (!$this->statusColumn) {
            $this->statusColumn = $this->getLayout()->createBlock(
                StatusColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->statusColumn;
    }

    /**
     * @return ColorRenderer&BlockInterface
     * @throws LocalizedException
     */
    private function getColorRenderer()
    {
        if (!$this->colorRenderer) {
            $this->colorRenderer = $this->getLayout()->createBlock(
                ColorRenderer::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->colorRenderer;
    }
}
