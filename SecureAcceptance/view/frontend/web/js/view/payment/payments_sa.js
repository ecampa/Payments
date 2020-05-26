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

        if (saType === 'flex') {
            rendererList.push(
                {
                    type: 'payments_sa',
                    component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/microform'
                }
            );
            return Component.extend({});
        }

        if (!window.checkoutConfig.payment.payments_sa.iframe_post) {
           
            rendererList.push(
                {
                    type: 'payments_sa',
                    component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/payments_sa'
                }
            );
            return Component.extend({});
        }

        if (window.checkoutConfig.payment.payments_sa.silent_post) {
            rendererList.push(
                {
                    type: 'payments_sa',
                    component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/iframe'
                }
            );
            return Component.extend({});
        }

        if (window.checkoutConfig.payment.payments_sa.use_iframe) {
            rendererList.push(
                {
                    type: 'payments_sa',
                    component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/hosted/iframe'
                }
            );
            return Component.extend({});
        }

        rendererList.push(
            {
                type: 'payments_sa',
                component: 'Payments_SecureAcceptance/js/view/payment/method-renderer/hosted/redirect'
            }
        );
        return Component.extend({});
    }
);
