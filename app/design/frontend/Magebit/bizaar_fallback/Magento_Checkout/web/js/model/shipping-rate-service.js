define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
    'underscore'
], function (quote, defaultProcessor, customerAddressProcessor, _) {
    'use strict';

    var processors = {};

    processors.default =  defaultProcessor;
    processors['customer-address'] = customerAddressProcessor;

    const shippingAddressChange =  _.debounce(() => {
        var type = quote.shippingAddress().getType();

        if (processors[type]) {
            processors[type].getRates(quote.shippingAddress());
        } else {
            processors.default.getRates(quote.shippingAddress());
        }
    }, 1000, false);

    quote.shippingAddress.subscribe(function () {
        shippingAddressChange()
    });

    return {
        /**
         * @param {String} type
         * @param {*} processor
         */
        registerProcessor: function (type, processor) {
            processors[type] = processor;
        }
    };
});
