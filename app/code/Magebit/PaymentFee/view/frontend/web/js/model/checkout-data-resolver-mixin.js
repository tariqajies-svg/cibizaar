define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method'
], function (
    wrapper,
    checkoutData,
    paymentService,
    selectPaymentMethodAction
) {
    'use strict';

    return function (checkoutDataResolver) {
        checkoutDataResolver.resolvePaymentMethod = wrapper.wrapSuper(checkoutDataResolver.resolvePaymentMethod, function () {
            this._super();
            var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                selectedPaymentMethod = checkoutData.getSelectedPaymentMethod(),
                selectedPaymentMethodConfig = window.checkoutConfig.selectedPaymentMethod;

            if (!selectedPaymentMethod && selectedPaymentMethodConfig) {
                selectedPaymentMethod = selectedPaymentMethodConfig;
            }

            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) {
                        selectPaymentMethodAction(payment);
                    }
                });
            }
        });

        return checkoutDataResolver;
    };
});
