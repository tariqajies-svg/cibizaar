/**
 * This file is part of the Magebit/bizaar_fallback design package.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magebit/bizaar
 * to newer versions in the future.
 *
 * @copyright Copyright (c) 2022 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Overriden to hide post code for countries that it's optional for
define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            imports: {
                countryOptions: '${ $.parentName }.country_id:indexedOptions',
                update: '${ $.parentName }.country_id:value'
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super();

            /**
             * equalityComparer function
             *
             * @returns boolean.
             */
            this.value.equalityComparer = function (oldValue, newValue) {
                return !oldValue && !newValue || oldValue === newValue;
            };

            return this;
        },

        /**
         * Method called every time country selector's value gets changed.
         * Updates all validations and requirements for certain country.
         * @param {String} value - Selected country ID.
         */
        update: function (value) {
            var isZipCodeOptional,
                option;

            if (!value) {
                return;
            }

            option = _.isObject(this.countryOptions) && this.countryOptions[value];

            if (!option) {
                return;
            }

            isZipCodeOptional = !!option['is_zipcode_optional'];

            if (isZipCodeOptional) {
                this.hide();
                this.error(false);
            } else {
                this.show();
            }


            this.validation['required-entry'] = !isZipCodeOptional;
            this.required(!isZipCodeOptional);
        }
    });
});
