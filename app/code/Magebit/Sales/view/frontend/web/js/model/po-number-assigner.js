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
    'jquery'
], function($) {
    'use strict';

    /** Override default place order action and add agreement_ids to request */
    return function(paymentData) {
        const poNumberInput = $('.payment-method._active .order-po-number.form input');

        /** Return if no input or empty */
        if (!poNumberInput || !poNumberInput.length || !poNumberInput.val()) {
            return
        }

        paymentData['po_number'] = poNumberInput.val();
    };
});
