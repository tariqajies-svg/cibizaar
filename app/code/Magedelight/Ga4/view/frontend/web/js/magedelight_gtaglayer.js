/**
 * Magedelight
 * Copyright (C) 2023 Magedelight <info@Magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Ga4
 * @copyright Copyright (c) 2023 Mage Delight (http://www.Magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@Magedelight.com>
 */

define([
    'jquery',
    'Magento_Ui/js/lib/core/storage/local',
    'underscore',
    'uiRegistry'
], function ($, localStorage, _,  registry) {
    "use strict";
    var gTagLayer = {
        storageDataExpiryTime : 10,
        localStorage : registry.get('localStorage'),

        init: function (options) {
            this.storageDataExpiryTime = options.storageDataExpiryTime || this.storageDataExpiryTime;
        },

        setItem: function (key, value) {
            var storedDataValue = {
                expiryDateTime: new Date(),
                value: value
            };

            this.localStorage.set(key, storedDataValue);
        },

        getItem: function (key) {
            var storedDataValue = this.localStorage.get(key);
            if (typeof storedDataValue !== 'undefined') {
                if (this.isDataExpired(storedDataValue.expiryDateTime)) {
                    this.removeDataItem(key);
                    return false;
                }

                return storedDataValue.value;
            }

            return false;
        },

        removeDataItem: function (key) {
            this.localStorage.remove(key);
        },

        isDataExpired: function (date) {
            var currentDate = new Date();
            var newDate = new Date(date);

            var dateDiff = (currentDate.getTime() - newDate.getTime()) / 1000;
            dateDiff /= 60;
            dateDiff = Math.abs(Math.round(dateDiff));

            return dateDiff > this.storageDataExpiryTime;
        }
    };

    return gTagLayer;
});