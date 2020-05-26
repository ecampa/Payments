define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/action/select-payment-method'
], function ($, VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Payments_SecureAcceptance/payment/vault-form',
            additionalData: {}
        },

        
        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'public_hash': this.publicHash
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
            data['additional_data']['cvv'] = $(this.getSelector('cc_cid')).val();

            return data;
        },
        
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        
        getCardType: function () {
            return this.details.type;
        },

        getTitle: function () {
            return this.details.title;
        },

        getSelector: function (field) {
            return '#' + this.getId() + '_' + field;
        },

        validate: function () {
            var $form = $(this.getSelector('form'));
            return $form.validation() && $form.validation('isValid');
        },

        getIsCvvEnabled: function () {
            return window.checkoutConfig.payment[this.getCode()].is_cvv_enabled;
        }
    });
});
