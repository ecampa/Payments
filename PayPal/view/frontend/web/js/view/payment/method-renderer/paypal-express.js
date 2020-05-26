define([
    'Payments_PayPal/js/view/payment/method-renderer/paypal-express-abstract',
    'Magento_Vault/js/view/payment/vault-enabler'
], function (Component, VaultEnabler) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Payments_PayPal/payment/paypal-express'
        },

        
        initialize: function () {
            this._super();

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return this;
        },

        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        getVaultCode: function () {
            return this.getMethodConfig().vaultCode;
        },

        
        getData: function () {
            var data = this._super();

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        }
    });
});
