define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';

    $.validator.addMethod(
        'microform-card-valid',
        function (value, element, params) {
            var input = $(params.selector), valid;

            if (!input.length && params.selector) {
                return false;
            }
            valid = String(input.attr('data-valid')).toLowerCase(input.attr('data-valid')) === "true";

            input.removeClass(params.errorClass);

            if (!valid) {
                input.addClass(params.errorClass);
            }

            return valid;
        },
        $.mage.__('Please enter a valid credit card number.')
    );

    return $.validator;
});
