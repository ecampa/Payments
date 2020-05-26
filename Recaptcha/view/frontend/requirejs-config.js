var config = {
    map: {
        '*': {
            'Payments_SecureAcceptance/template/payment/iframe.html': 'Payments_Recaptcha/template/payment/iframe.html',
            'Payments_SecureAcceptance/template/payment/hosted/iframe.html': 'Payments_Recaptcha/template/payment/hosted/iframe.html',
            'Payments_SecureAcceptance/template/payment/hosted/redirect.html': 'Payments_Recaptcha/template/payment/hosted/redirect.html'
        }
    },
    config: {
        mixins: {
            'Payments_SecureAcceptance/js/view/payment/method-renderer/iframe': {
                'Payments_Recaptcha/js/iframe-mixin': true
            }
        }
    }
};
