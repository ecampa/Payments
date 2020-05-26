define([
        'jquery',
        'transparent'
    ],
    function ($) {
        $.widget('payments.transparent_redirect', $.mage.transparent, {
            options: {
                context: null,
                placeOrderSelector: '[data-role="review-save"]',
                paymentFormSelector: '#co-payment-form',
                updateSelectorPrefix: '#checkout-',
                updateSelectorSuffix: '-load',
                reviewAgreementForm: '#checkout-agreements',
                cgiUrl: null,
                orderSaveUrl: null,
                controller: null,
                gateway: null,
                dateDelim: null,
                cardFieldsMap: null,
                expireYearLength: 2,
                hiddenFormTmpl:
                    '<form action="<%= data.action %>" ' +
                    'method="POST" hidden ' +
                    'enctype="application/x-www-form-urlencoded" class="no-display"> ' +
                    '<% _.each(data.inputs, function(val, key){ %>' +
                    '<input value="<%= val %>" name="<%= key %>" type="hidden"> ' +
                    '<% }); %>' +
                    '</form>'
            },
            _preparePaymentData: function(data) {
                return data;
            }
        });

        return $.payments.transparent_redirect;
    });
