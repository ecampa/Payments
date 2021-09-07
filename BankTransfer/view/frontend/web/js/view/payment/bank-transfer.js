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
                type: 'payments_bank_transfer_ideal',
                component: 'Payments_BankTransfer/js/view/payment/method-renderer/bank-transfer-ideal'
            },
            {
                type: 'payments_bank_transfer_sofort',
                component: 'Payments_BankTransfer/js/view/payment/method-renderer/bank-transfer',
                config: {
                    code: 'payments_bank_transfer_sofort',
                    template: 'Payments_BankTransfer/payment/bank-transfer'
                }
            },
            {
                type: 'payments_bank_transfer_bancontact',
                component: 'Payments_BankTransfer/js/view/payment/method-renderer/bank-transfer',
                config: {
                    code: 'payments_bank_transfer_bancontact',
                    template: 'Payments_BankTransfer/payment/bank-transfer'
                }
            });
        
        return Component.extend({});
    }
);


