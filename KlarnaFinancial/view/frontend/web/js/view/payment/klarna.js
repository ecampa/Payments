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
                type: 'payments_klarna',
                component: 'Payments_KlarnaFinancial/js/view/payment/method-renderer/klarna'
            }
        );

        
        return Component.extend({});
    }
);
