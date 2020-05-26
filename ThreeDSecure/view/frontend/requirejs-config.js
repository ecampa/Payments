var config = {
    config: {
        mixins: {
            'Payments_SecureAcceptance/js/view/payment/method-renderer/iframe': {
                'Payments_ThreeDSecure/js/view/payment/iframe-mixin': true
            },
            'Payments_SecureAcceptance/js/view/payment/method-renderer/microform': {
                'Payments_ThreeDSecure/js/view/payment/microform-mixin': true
            },
            'Payments_SecureAcceptance/js/view/payment/method-renderer/vault': {
                'Payments_ThreeDSecure/js/view/payment/vault-mixin': true
            },
            'Magento_Checkout/js/model/error-processor': {
                'Payments_ThreeDSecure/js/model/error-processor-mixin': true
            }
        }
    }
};
