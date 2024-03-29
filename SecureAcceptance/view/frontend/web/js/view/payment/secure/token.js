define(
    [
        'jquery',
        'uiComponent'
    ],
    function (
        $,
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Payments_SecureAcceptance/payment/secure/token'
            },
            getSecureToken: function () {
                return window.checkoutConfig.payment.payments_sa.secure_token;
            }
        });
    }
);
