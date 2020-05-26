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
                type: 'payments_visa',
                component: 'Payments_VisaCheckout/js/view/payment/method-renderer/vc'
            }
        );
        
        return Component.extend({});
    }
);
