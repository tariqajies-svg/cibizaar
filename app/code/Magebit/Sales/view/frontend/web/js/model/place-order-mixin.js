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
    'jquery',
    'mage/utils/wrapper',
    'Magebit_Sales/js/model/po-number-assigner'
], function ($, wrapper, agreementsAssigner) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add po_number to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            agreementsAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
