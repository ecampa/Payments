define([
    'jquery',
    'Payments_SecureAcceptance/js/view/payment/method-renderer/sa/redirect',
    'Magento_Checkout/js/model/full-screen-loader',
    'Payments_SecureAcceptance/js/action/cancel'
], function ($, Component, fullScreenLoader, cancelAction) {
    return Component.extend({
        defaults: {
            active: false,
            template: 'Payments_SecureAcceptance/payment/sa/iframe',
            code: 'payments_sa'
        },
        initTimeoutHandler: function () {
           
        },
        iframeLoadHandler: function () {
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.stopLoader(true);
        },
        iframeReturnHandler: function(){
            fullScreenLoader.startLoader();
        },
        iframeCloseHandler: function () {
            fullScreenLoader.stopLoader(true);
            this.isPlaceOrderActionAllowed(true);
        },
        iframeCloseBtnHandler: function () {
            fullScreenLoader.startLoader(true);
            cancelAction();
        }
    });
});
