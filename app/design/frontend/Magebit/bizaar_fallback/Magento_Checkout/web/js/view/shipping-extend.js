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
define([
    'ko',
    'Magento_Checkout/js/checkout-data',
    'uiRegistry'
    ], function (ko, checkoutData, registry) {
    'use strict';

    var mixin = {
        jebelAliTestCase: 'jebel ali',
        isFreeZone: ko.observable(false),
        isFreeZoneShown: ko.observable(true),
        stateValue: ko.observable({
            city: '',
            region: '',
            street: {}
        }),

        initialize: function () {
            this.observeCountry();
            this.isFreeZone.subscribe((value) => this.jebelAliPostCode(value));

            return this._super();
        },

        jebelAliPostCode: function(activate) {
            if (!this.isFreeZoneShown()) return;
            // Use 0001 post code to activate tax rule that will remove tax from the order
            const addressData = this.source.get('shippingAddress');
            addressData['postcode'] = activate ? '0001' : undefined;
            this.source.set('shippingAddress', addressData);
        },

        observeCountry: function() {
            const self = this;
            registry.async('checkoutProvider')(function (checkoutProvider) {
                checkoutProvider.on('shippingAddress', function (addressData) {
                    self.isFreeZoneShown(addressData.country_id === 'AE');

                    if ((self.stateValue().region !== addressData.region && addressData.region.toLowerCase().includes(self.jebelAliTestCase)) ||
                        (self.stateValue().city !== addressData.city && addressData.city.toLowerCase().includes(self.jebelAliTestCase)) ||
                        (self.stateValue().street[0] !== addressData.street[0] && addressData.street[0].toLowerCase().includes(self.jebelAliTestCase)) ||
                        (self.stateValue().street[1] !== addressData.street[1] && addressData.street[1].toLowerCase().includes(self.jebelAliTestCase))
                    ) {
                        self.isFreeZone(true);
                        self.stateValue(addressData);
                    }
                })
            })
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
