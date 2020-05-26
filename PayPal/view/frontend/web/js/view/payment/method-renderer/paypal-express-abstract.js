define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Payments_PayPal/js/action/set-payment-method',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function ($, Component, setPaymentMethodAction, additionalValidators, quote, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Payments_PayPal/payment/paypal-express-bml'
        },

        
        showAcceptanceWindow: function (data, event) {
            window.open(
                $(event.currentTarget).attr('href'),
                'olcwhatispaypal',
                'toolbar=no, location=no,' +
                ' directories=no, status=no,' +
                ' menubar=no, scrollbars=yes,' +
                ' resizable=yes, ,left=0,' +
                ' top=0, width=400, height=350'
            );

            return false;
        },

        
        getPaymentAcceptanceMarkHref: function () {
            return this.getMethodConfig().paymentAcceptanceMarkHref;
        },

        
        getPaymentAcceptanceMarkSrc: function () {
            return this.getMethodConfig().paymentAcceptanceMarkSrc;
        },

        
        getData: function () {
            var parent = this._super(),
                additionalData = {};

            return $.extend(true, parent, {
                'additional_data': additionalData
            });
        },

        
        continueToPayPal: function () {
            var self = this;
            if (additionalValidators.validate()) {
               
                this.selectPaymentMethod();
                setPaymentMethodAction(this.messageContainer).done(
                    function () {
                        customerData.invalidate(['cart']);
                        $.mage.redirect(
                            self.getMethodConfig().redirectUrl[quote.paymentMethod().method]
                        );
                    }
                );

                return false;
            }
        },

        getMethodConfig: function () {
            return window.checkoutConfig.payment.payments_paypal;
        }
    });
});
