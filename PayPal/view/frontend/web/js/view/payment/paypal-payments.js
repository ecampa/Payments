define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'payments_paypal',
            component: 'Payments_PayPal/js/view/payment/method-renderer/paypal-express'
        },
        {
            type: 'payments_paypal_credit',
            component: 'Payments_PayPal/js/view/payment/method-renderer/paypal-express-bml'
        }
    );

    
    return Component.extend({});
});
