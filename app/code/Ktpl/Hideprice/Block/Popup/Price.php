<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Hide Price Hyva Compatibility
 */

namespace Ktpl\Hideprice\Block\Popup;

use Amasty\HidePrice\Helper\Data;
use Amasty\HidePrice\Model\ConfigProvider;
use Amasty\HidePrice\Model\Source\ReplaceButton;
use Magento\Framework\View\Element\Template;

class Price extends \Amasty\HidePriceHyva\Block\Popup\Price
{
    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $configProvider, $data);
        $this->configProvider = $configProvider;
    }

    protected function _beforeToHtml(): Price
    {
        if ($this->configProvider->getHidePriceLink() === Data::HIDE_PRICE_POPUP_IDENTIFICATOR
            && (int)$this->configProvider->getReplaceButtonType() !== ReplaceButton::HIDE_PRICE_POPUP
        ) {
            $this->setTemplate('Ktpl_Hideprice::product/popup.phtml');
        }

        return parent::_beforeToHtml();
    }
}
