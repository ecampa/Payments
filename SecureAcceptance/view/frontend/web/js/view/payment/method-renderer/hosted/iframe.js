define([
    'jquery',
    'Payments_SecureAcceptance/js/view/payment/method-renderer/iframe',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, Component, fullScreenLoader) {
    return Component.extend({
        defaults: {
            active: false,
            template: 'Payments_SecureAcceptance/payment/hosted/iframe',
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
        }

    });
});
