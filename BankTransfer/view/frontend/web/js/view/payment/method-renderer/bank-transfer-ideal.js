define(
    [
        'jquery',
        'ko',
        'Payments_BankTransfer/js/view/payment/method-renderer/bank-transfer',
        'mage/url'
    ],
    function ($, ko, Component, urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Payments_BankTransfer/payment/bank-transfer-ideal',
                code: 'payments_bank_transfer_ideal',
                bankList: ko.observableArray()
            },
            initialize: function () {
                this._super();
                this.observe(['bankList']);
                var me = this;
                var url = urlBuilder.build('paymentsbt/index/info');
                $.getJSON(url, function (data) {
                    var listArray = [];
                    var i = 0;
                    for (var bankCode in data) {
                        listArray[i] = {
                            code: bankCode,
                            name: data[bankCode]
                        };
                        i++;
                    }
                    me.bankList(listArray);
                });
            },
            getBankCode: function(){
                return $('#bank-select').val();
            }
        });
    }
);


