define([
    'jquery',
    'underscore',
    'Magento_Vault/js/view/payment/method-renderer/vault'
], function ($, _, VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Payments_PayPal/payment/vault',
            additionalData: {}
        },

        
        getPayerEmail: function () {
            return this.details.email;
        },
        
        getMaskedToken: function(){
            return this.maskedToken;
        },
        
        getPaymentIconSrc: function () {
            return window.checkoutConfig.payment[this.getPaymentProviderCode()].paymentAcceptanceMarkSrc;
        },
        
        getData: function () {
            var data = {
                'method': this.code,
                'additional_data': {
                    'public_hash': this.publicHash
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            return data;
        },

        getPaymentProviderCode: function () {
            return 'payments_paypal';
        }
    });
});
