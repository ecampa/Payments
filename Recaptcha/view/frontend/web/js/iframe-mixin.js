define([
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, additionalValidators) {
    'use strict';

    return function (Component) {
        return Component.extend(
            {
                placeOrder: function () {
                    var isEnabled = window.checkoutConfig.msp_recaptcha.enabled.payments,
                        $form = $('#co-payment-form'),
                        _super = this._super.bind(this)
                    ;

                    if (!this.validateHandler() || !additionalValidators.validate() || !isEnabled) {
                        return _super();
                    }

                    $form
                        .off('payments:endRecaptcha')
                        .on('payments:endRecaptcha', function () {
                               
                                _super();
                                $form.off('payments:endRecaptcha');
                            }.bind(this)
                        );

                    $form.trigger('payments:startRecaptcha');
                }
            }
        );
    };
});
