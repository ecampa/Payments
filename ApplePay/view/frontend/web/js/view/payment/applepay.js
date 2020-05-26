define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        if (window.ApplePaySession && window.ApplePaySession.canMakePayments) {
            rendererList.push({
                type: 'payments_applepay',
                component: 'Payments_ApplePay/js/view/payment/method-renderer/applepay'
            });
        }
        
        return Component.extend({});
    }
);


