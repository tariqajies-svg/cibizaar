<?php
/**
 * This file is part of the Magebit_Customer package.
 *
 * @copyright Copyright (c) 2023 Magebit, Ltd. (https://magebit.com/)
 * @author Magebit <info@magebit.com>
 * @license MIT
 */

declare(strict_types=1);

namespace Magebit\Customer\Block\Widget;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Widget for showing customer telephone.
 *
 * @method CustomerInterface getObject()
 * @method Name setObject(CustomerInterface $customer)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class CustomerTelephone extends \Magento\Customer\Block\Widget\Telephone
{
    /**
     * the attribute code
     */
    const ATTRIBUTE_CODE = 'customer_telephone'; // phpcs:ignore

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Magebit_Customer::widget/customer_telephone.phtml');
    }
}
