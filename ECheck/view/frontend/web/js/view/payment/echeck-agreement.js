define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Payments_ECheck/js/model/agreements-modal'
], function (ko,
             $,
             Component,
             quote,
             agreementsModal) {
    'use strict';

    return Component.extend({

        defaults: {
            code: 'payments_echeck'
        },

        customerName: ko.computed(function() {
            if(quote.shippingAddress()){
                return quote.shippingAddress().firstname + " " + quote.shippingAddress().lastname;
            }
        }),

        getCode: function () {
            return this.code;
        },

        
        initModal: function (element) {
            agreementsModal.createModal(element);
        },

        
        showContent: function (element) {
            agreementsModal.showModal();
        },

        getDate: function () {
            return window.checkoutConfig.payment[this.getCode()].localeDate;
        },

        getStorePhone: function () {
            return window.checkoutConfig.payment[this.getCode()].storePhone;
        },

        getQuote: function () {
            return quote;
        },

        isAgreementRequired: function () {
            return !!parseInt(window.checkoutConfig.payment[this.getCode()].agreementRequired);
        }

    });
});
