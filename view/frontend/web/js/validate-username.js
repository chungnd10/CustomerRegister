define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function () {
        $.validator.addMethod("username", function (value, element) {
                var username = $(element).val();
                var pattern = /^[a-z0-9]{6,20}$/;
                if (!pattern.test(String(username))) {
                    return false;
                } else {
                    return true;
                }
            },
            $.mage.__("The username consists of lowercase letters and numbers, ranging from 6-20 characters.")
        );
    }
});
