define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Checkout/js/checkout-data',
    'mageUtils'
], function ($,  ko, Element, validator, checkoutData, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            uid: utils.uniqueid(),
            phoneCode: '',
            phoneNumber: '',
        },
        countryList: ko.observable([]),
        searchResults: ko.observable([]),
        dropdownOpened: ko.observable(false),

        initialize: function () {
            this._super().observe(['phoneCode', 'phoneNumber']);
            this.countryList(window.CountryList.getAll());

            if (this.value()) {
                const splittedNumber = this.value().split(' ');

                if (splittedNumber.length > 1) {
                    this.phoneCode(splittedNumber[0]);
                    this.phoneNumber(splittedNumber[1]);
                } else {
                    this.phoneNumber(this.value());
                }
            }

            let self = this;

            $(document).on("click", function (event) {
                if (self.dropdownOpened() && event.target.id !== 'phoneCode' && event.target.id !== 'codeContainer') {
                    self.dropdownOpened(false);
                    self.validateCode();
                    self.generateFullNumber();
                }
            });
        },

        onUpdate: function () {
            this.bubble('update', this.hasChanged());
        },

        generateFullNumber() {
            this.value(this.phoneCode() + ' ' + this.phoneNumber().replace(/\s+/g, ''));
        },

        filteredOptions() {
            return this.countryList().filter(option => {
                return (option.data.dial_code.indexOf(this.phoneCode()) > -1)
            })
        },

        matchCode () {
            return this.countryList().filter(option => {
                return (option.data.dial_code === this.phoneCode())
            })
        },

        validateCode () {
            if (this.phoneCode().charAt(0) !== '+') {
                this.phoneCode('+' + this.phoneCode());
            }

            if (this.phoneCode().charAt(0) !== '+' || !this.matchCode().length) {
                this.phoneCode('');
            }
        },

        validate: function () {
            let phoneCodeResult = validator(this.validation, this.phoneCode(), this.validationParams),
                phoneNumberResult = validator(this.validation, this.phoneNumber(), this.validationParams),
                message = !this.disabled() && this.visible() ? (phoneCodeResult.message || phoneNumberResult.message) : '',
                isValid = this.disabled() || !this.visible() || (phoneCodeResult.passed && phoneNumberResult.passed);

            this.error(message);
            this.error.valueHasMutated();
            this.bubble('error', message);

            if (this.source && !isValid) {
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },
    });
});
