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

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Magebit_Sales/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Magebit_Sales/js/model/set-payment-information-mixin': true
            }
        }
    }
};
