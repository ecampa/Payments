define([
    'Payments_PayPal/js/view/payment/method-renderer/paypal-express-abstract'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Payments_PayPal/payment/paypal-express-bml'
        },

        getCreditTitle: function () {
            return this.getMethodConfig().creditTitle;
        }
    });
});
