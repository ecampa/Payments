define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/cookies'
], function ($, urlBuilder, globalMessageList) {
    'use strict';

    return function () {
        var form = $('<form '
            + 'action="' + urlBuilder.build('paymentssa/index/cancel', {}) + '" '
            + 'method="post">'
            + '</form>');
        $('body').append(form);
        form.submit();
    };
});
