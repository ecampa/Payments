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

        var saType = window.checkoutConfig.payment.payments_sa.sa_type;

        if (window.checkoutConfig.payment.payments_sa.iframe_post || saType === 'flex') {
            return Component.extend({});
        }

        if (
            window.checkoutConfig.payment.payments_sa.silent_post
            || !window.checkoutConfig.payment.payments_sa.use_iframe
        ) {
            rendererList.push(
                {
                    type: 'payments_sa',
                    component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/sa/redirect'
                }
            );
            return Component.extend({});
        }

        rendererList.push(
            {
                type: 'payments_sa',
                component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/sa/iframe'
            }
        );
        return Component.extend({});
    }
);
