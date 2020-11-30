define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function () {
        $.validator.addMethod("phonenumber_vietnam", function (value, element) {
                var phone_number = $(element).val();
                var pattern = /(02|03|05|07|08|09)+([0-9]{8,9})\b/;
                if (!pattern.test(String(phone_number))) {
                    return false;
                } else {
                    return true;
                }
            },
            $.mage.__("Bạn phải nhập số điện thoại bắt đầu bằng 02 hoặc 03, 05, 07, 08, 09 và dài từ 10-11 ký tự")
        );
    }
});
