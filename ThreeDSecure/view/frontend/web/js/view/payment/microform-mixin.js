define([
    'jquery',
    'mage/utils/wrapper',
    'Payments_ThreeDSecure/js/view/payment/payer-authentication',
    'Payments_ThreeDSecure/js/view/payment/pa-enabler'
], function ($, wrapper, pa, Enabler) {
    'use strict';

    return function (Component) {

        if (!Enabler.isEnabled('payments_sa')) {
            return Component;
        }

        return Component.extend({
            initialize: function () {
                this._super();
                pa.initialize(this.getCode());
            },
            placeOrderContinue: function (data, event, _super) {
                var cardBin;

                if (this.microformResponse && this.microformResponse.maskedPan) {
                    cardBin = this.microformResponse.maskedPan.substr(0, 6);
                }

                pa.placeOrder(this, _super, cardBin);
            },
            getData: function () {
                var data = this._super();

                pa.visitData(this, data);
                return data;
            }
        });

    };
});
