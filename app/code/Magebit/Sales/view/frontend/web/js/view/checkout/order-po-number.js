/**
 * This file is part of the Magebit_Sales package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit_Sales
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2024 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

define([
    'uiComponent',
    'Magento_Customer/js/model/customer'
], function(Component, customer) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magebit_Sales/checkout/order-po-number',
            label: '',
        },

        isVisible: function() {
            return !!customer.customerData.extension_attributes.aw_ca_company_user;
        }
    });
});
