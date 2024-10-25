define([
    'jquery',
    'mage/url',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($, urlBuilder){
    'use strict';
    return function() {
        var temp = false;
        console.log("ktplll");
        $.validator.addMethod(
            "accept_terms",
            function(value, element) {
                return $(element).is(':checked')
            },
            $.mage.__("Please indicate that you have read and accepted the Terms of Use.")
        );

        $.validator.addMethod(
            "valid_url",
            function(value, element) {
                return /^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(value)
            },
            $.mage.__("Please enter valid URL.")
        );

        $.validator.addMethod(
            'validate-open-emails',
            function (v) {
                var reg = /^([\w-\.]+@(?!gmail.com)(?!yahoo.com)(?!hotmail.com)(?!yahoo.co.in)(?!live.com)(?!outlook.com)(?!msn.com)(?!ymail.com)(?!icloud.com)(?!qq.com)(?!163.com)([\w-]+\.)+[\w-]{2,6})?$/i;
                return reg.test(v);
            },
            $.mage.__('Please enter a company email.')
        );

        $.validator.addMethod(
            'validate-existing-email',
            function (value) {
                $.ajax({
                    url: urlBuilder.build('rest/V1/customers/isEmailAvailable'),
                    type: 'POST',
                    async: false,
                    contentType: "application/json; charset=utf-8",
                    showLoader: true,
                    data: JSON.stringify({
                        customerEmail: value
                    }),
                    success: function(response) {
                        temp = response;
                    }
                });
                return temp;
            },
            $.mage.__('An account with the same email already exists.')
        );
        $.validator.addMethod(
            "required",
            function (value) {
                console.log("value",value);
            }
        );
        $.validator.addMethod(
            "input_tag_rule",
            function(value) {
                return !/<script|<style|<iframe|<frame|<html|<h1|<h2|<h3|<h4|<h5|<h6/.test(value)
            },
            $.mage.__("The field should not contain special characters and html tags..")
        );
    }
});