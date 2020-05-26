define(
    [
        'underscore',
        'jquery',
        'Payments_PayPal/js/view/payment/method-renderer/paypal-express-abstract',
        'Payments_PayPal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/lib/view/utils/dom-observer',
        'paypalInContextExpressCheckout',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        _,
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        domObserver,
        paypalExpressCheckout,
        customerData,
        messageList
    ) {
        'use strict';

       
        var clientInit = false;

        return Component.extend({

            defaults: {
                template: 'Payments_PayPal/payment/paypal-express-in-context',
                clientConfig: {
                    
                    click: function (event) {
                        event.preventDefault();

                        if (additionalValidators.validate()) {
                            paypalExpressCheckout.checkout.initXO();

                            this.selectPaymentMethod();
                            setPaymentMethodAction(this.messageContainer).done(function () {
                                $('body').trigger('processStart');

                                $.getJSON(this.path, {
                                    button: 0
                                }).done(function (response) {
                                    var message = response && response.message;

                                    if (message) {
                                        if (message.type === 'error') {
                                            messageList.addErrorMessage({
                                                message: message.text
                                            });
                                        } else {
                                            messageList.addSuccessMessage({
                                                message: message.text
                                            });
                                        }
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
                            }.bind(this));
                        }
                    }
                }
            },

            
            initialize: function () {
                this._super();
                this.initClient();

                return this;
            },

            
            initClient: function () {
                var selector = '#' + this.getButtonId();

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);

                if (!clientInit) {
                    domObserver.get(selector, function () {
                        paypalExpressCheckout.checkout.setup(this.merchantId, this.clientConfig);
                        clientInit = true;
                        domObserver.off(selector);
                    }.bind(this));
                } else {
                    domObserver.get(selector, function () {
                        $(selector).on('click', this.clientConfig.click);
                        domObserver.off(selector);
                    }.bind(this));
                }

                return this;
            },

            
            getButtonId: function () {
                return this.inContextId;
            }
        });
    }
);
