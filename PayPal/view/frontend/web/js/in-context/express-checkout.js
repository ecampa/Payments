define([
    'underscore',
    'jquery',
    'uiComponent',
    'paypalInContextExpressCheckout',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function (_, $, Component, paypalExpressCheckout, customerData) {
    'use strict';

    return Component.extend({

        defaults: {
            clientConfig: {

                checkoutInited: false,

                
                click: function (event) {
                    $('body').trigger('processStart');

                    event.preventDefault();

                    if (!this.clientConfig.checkoutInited) {
                        paypalExpressCheckout.checkout.initXO();
                        this.clientConfig.checkoutInited = true;
                    } else {
                        paypalExpressCheckout.checkout.closeFlow();
                    }

                    $.getJSON(this.path, {
                        button: 1
                    }).done(function (response) {
                        var message = response && response.message;

                        if (message) {
                            customerData.set('messages', {
                                messages: [message]
                            });
                        }

                        if (response && response.url) {
                            paypalExpressCheckout.checkout.startFlow(response.url);

                            return;
                        }

                        paypalExpressCheckout.checkout.closeFlow();
                    }).fail(function () {
                        paypalExpressCheckout.checkout.closeFlow();
                    }).always(function () {
                        $('body').trigger('processStop');
                        customerData.invalidate(['cart']);
                    });
                }
            }
        },

        
        initialize: function () {
            this._super();

            return this.initClient();
        },

        
        initClient: function () {
            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);

            paypalExpressCheckout.checkout.setup(this.merchantId, this.clientConfig);

            return this;
        }
    });
});
