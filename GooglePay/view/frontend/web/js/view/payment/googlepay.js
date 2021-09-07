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
                type: 'payments_googlepay',
                component: 'Payments_GooglePay/js/view/payment/method-renderer/googlepay-method'
            }
        );
        
        return Component.extend({});
    }
);
