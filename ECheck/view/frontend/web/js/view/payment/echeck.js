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
                type: 'payments_echeck',
                component: 'Payments_ECheck/js/view/payment/method-renderer/echeck-method'
            }
        );
        
        return Component.extend({});
    }
);
