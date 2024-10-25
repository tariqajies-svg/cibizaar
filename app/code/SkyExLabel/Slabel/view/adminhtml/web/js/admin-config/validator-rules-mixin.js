define([
 'jquery'
], function ($) {
    'use strict';
    return function (target) {
        $.validator.addMethod(
            'validate-token',
            function (value) {
                return !(value.length > 10);
            },
            $.mage.__('Please enter a 10 digit number.')
        );
        return target;
    };
});