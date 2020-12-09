define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function () {

        // Validate username
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

        // Validate tax code
        $.validator.addMethod("tax_code", function (value, element) {

                var valueRadio = $("input[name='type_customer']:checked").attr('data-value');

                if (valueRadio == 'business') {
                    var valueTax = $("#tax_code").val();
                    if (valueTax == '') {
                        return false;
                    } else {
                        return true;
                    }
                }
            },
            $.mage.__("Đây là trường bắt buộc.")
        );

        // Validate note
        $.validator.addMethod("note", function (value, element) {

                var atLeastOneIsChecked = $('input[name="is_agency[]"]:checked').length > 0;

                if (atLeastOneIsChecked === true) {
                    var valueTax = $("#note").val();

                    if (valueTax.trim() === '') {
                        return false;
                    } else {
                        return true;
                    }
                }
            },
            $.mage.__("Đây là trường bắt buộc.")
        );
    }
});
