define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/totals',
], function(Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magebit_PaymentFee/checkout/summary/payment_fee'
        },
        total_code: 'payment_fee',

        /**
         * Order totals
         *
         * @return {Object}
         */
        totals: totals.totals(),

        /**
         * Is display raf totals
         *
         * @return {Boolean}
         */
        isDisplayed: function() {
            return this.getPureValue() != 0;
        },

        /**
         * Get total title
         *
         * @returns {string}
         */
        getTitle: function() {
            if (this.totals) {
                const paymentFee = totals.getSegment(this.total_code);
                if (paymentFee) {
                    return paymentFee.title;
                }
            }

            return '';
        },

        /**
         * Get total pure value
         *
         * @return {Number}
         */
        getPureValue: function() {
            if (this.totals) {
                const paymentFee = totals.getSegment(this.total_code);
                if (paymentFee) {
                    return paymentFee.value;
                }
            }

            return 0;
        },

        /**
         * Get total value
         *
         * @return {String}
         */
        getValue: function() {
            return this.getFormattedPrice(this.getPureValue());
        },
    });
});
