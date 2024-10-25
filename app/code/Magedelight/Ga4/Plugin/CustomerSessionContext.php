<?php
/**
 *
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

namespace Magedelight\Ga4\Plugin;

use Magento\Framework\App\ActionInterface;
use Magedelight\Ga4\Helper\Data;

class CustomerSessionContext
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->helper = $helper;
    }

    /**
     * Set customer details to HTTP context
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {

        if ($this->helper->isGTMStatus() && $this->helper->getConfig(Data::XML_PATH_GA4_REALTIME_TRACKING) && $this->helper->getConfig(Data::XML_PATH_GA4_SHOW_EMAIL)) {
            $this->httpContext->setValue(
                'customer_id',
                $this->customerSession->getCustomerId(),
                false
            );

            $this->httpContext->setValue(
                'customer_name',
                $this->customerSession->getCustomer()->getName(),
                false
            );

            $this->httpContext->setValue(
                'customer_email',
                $this->customerSession->getCustomer()->getEmail(),
                false
            );
        }
    }
}
