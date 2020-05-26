define([
    'jquery',
    'mage/validation'
], function ($) {
    'use strict';

    var agreementsInputPath = '.payment-method._active div.echeck-agreement-block input';

    return {
        
        validate: function () {
            var isValid = true;

            if ($(agreementsInputPath).length === 0) {
                return true;
            }

            $(agreementsInputPath).each(function (index, element) {
                if (!$.validator.validateSingleElement(element, {
                    errorElement: 'div'
                })) {
                    isValid = false;
                }
            });

            return isValid;
        }
    };
});
