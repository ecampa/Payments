define([
    'jquery',
    'Payments_SecureAcceptance/js/view/payment/method-renderer/iframe'
], function ($, Component) {
    return Component.extend({
        defaults: {
            active: false,
            template: 'Payments_SecureAcceptance/payment/hosted/redirect',
            code: 'payments_sa'
        }
    });
});
