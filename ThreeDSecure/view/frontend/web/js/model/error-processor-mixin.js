define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, wrapper,  redirectOnSuccessAction, fullScreenLoader) {

    'use strict';

    return function (errorProcessor) {

        function getMethodCode(quote) {
            var code = (quote.paymentMethod() && quote.paymentMethod().method)
                ? quote.paymentMethod().method
                : 'payments_sa';

            return code.replace(/_(\d+)$/, '');
        }

        errorProcessor.process = wrapper.wrap(
            errorProcessor.process,
            function (originalProcess, response, messageContainer) {

                if (!response.responseJSON || !response.responseJSON.code || response.responseJSON.code !== 475) {
                    return originalProcess(response, messageContainer);
                }

                console.log('Proceeding to PA');

                require(['Payments_ThreeDSecure/js/view/payment/cardinal'], function (Cardinal) {
                    $.when(
                        Cardinal.continue(response.responseJSON.parameters.cca, response.responseJSON.parameters.order)
                    )
                        .then(
                            function (response) {
                                require(['Magento_Checkout/js/action/place-order', 'Magento_Checkout/js/model/quote'],
                                    function (placeOrderAction, quote) {
                                        placeOrderAction({
                                            'method': getMethodCode(quote),
                                            'extension_attributes': {'cca_response': response}
                                        }).done(
                                            function () {
                                                redirectOnSuccessAction.execute();
                                            }
                                        ).fail(
                                            function () {
                                                fullScreenLoader.stopLoader();
                                            }
                                        );
                                    });
                            }
                        )
                        .fail(
                            function (response) {
                                originalProcess(response, messageContainer);
                            }
                        );
                });
            });

        return errorProcessor;
    };
});
