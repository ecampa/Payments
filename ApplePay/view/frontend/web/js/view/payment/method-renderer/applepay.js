var appleSession = null;

define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-billing-address',
        'Payments_ApplePay/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        $,
        ko,
        quote,
        Component,
        setBillingAddress,
        setPaymentMethodAction,
        additionalValidators,
        urlBuilder,
        customerData
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Payments_ApplePay/payment/applepay',
                code: 'payments_applepay'
            },
            initialize: function () {
                this._super();

                this.appleRequest = null;
                var me = this;

                $.getJSON(urlBuilder.build('paymentsapple/index/request'), function(data){
                    if (data.request) {
                        me.appleRequest = data.request;
                    }
                });
                return this;
            },
            getCode: function () {
                return 'payments_applepay';
            },
            getTitle: function () {
                return window.checkoutConfig.payment[this.getCode()].title;
            },
            isActive: function () {
                return window.checkoutConfig.payment[this.getCode()].active;
            },
            validateMerchant: function(e) {
                $.post(urlBuilder.build('paymentsapple/index/validate'), {
                    url: e.validationURL,
                    form_key: $.cookie('form_key')
                }, function(data){
                    if (data.session) {
                        appleSession.completeMerchantValidation(data.session);
                    }
                }, 'json');
            },
            paymentAuthorized: function(e) {
                $.post(urlBuilder.build('paymentsapple/index/placeorder'), {
                    payment: e.payment,
                    guestEmail: customerData.getValidatedEmailValue(),
                    form_key: $.cookie('form_key')
                }, function(response) {

                    if (response.status === 200) {
                        appleSession.completePayment(window.ApplePaySession.STATUS_SUCCESS);
                    }

                    window.location.replace(urlBuilder.build(response.redirect_url));

                }, 'json');
            },
            applePayRequest: function()
            {
                if (additionalValidators.validate() && this.appleRequest) {
                    appleSession = new window.ApplePaySession(2, this.appleRequest);
                    appleSession.onvalidatemerchant = this.validateMerchant;
                    appleSession.onpaymentauthorized = this.paymentAuthorized;
                    appleSession.begin();
                }
            }
        });
    }
);


