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
            placeOrder: function () {
                pa.placeOrder(this, this._super.bind(this), this.creditCardNumber(), this.creditCardType());
            },
            getData: function () {
                var data = this._super();

                pa.visitData(this, data);
                return data;
            }
        });

    };
});
