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
            $.mage.__("Tên tài khoản bao gồm các chữ cái viết thường và số, từ 6-20 ký tự.")
        );
    }
});
