define([
    "jquery",
    "jquery/ui",
    'mage/validation'
], function($) {
    "use strict";
    // console.log('custom.js is loaded!!');
        //creating jquery widget
        $.widget('custom.js', {
            _create: function() {
                this._bind();
            },

            /**
             * Event binding, will monitor change, keyup and paste events.
             * @private
             */
            _bind: function () {
                this._on(this.element, {
                    'change': this.validateField,
                    'keyup': this.validateField,
                    'paste': this.validateField,
                    'focusout': this.validateField
                });
            },

            validateField: function () {
                $.validator.validateSingleElement(this.element);
            },

        });

    return $.custom.js;
});