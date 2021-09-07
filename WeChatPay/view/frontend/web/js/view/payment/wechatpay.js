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

        rendererList.push(
            {
                type: 'payments_wechatpay',
                component: 'Payments_WeChatPay/js/view/payment/method-renderer/wechatpay-method'
            }
        );

        return Component.extend({});
    }
);
