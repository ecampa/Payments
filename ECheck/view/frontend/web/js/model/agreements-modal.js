define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return {
        modalWindow: null,

        
        createModal: function (element) {
            var options;

            this.modalWindow = element;
            options = {
                'type': 'popup',
                'modalClass': 'agreements-modal',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.show-modal',
                'buttons': [
                    {
                        text: $t('Close'),
                        class: 'action secondary action-hide-popup',

                        
                        click: function () {
                            this.closeModal();
                        }
                    }
                ]
            };
            modal(options, $(this.modalWindow));
        },

        
        showModal: function () {
            $(this.modalWindow).modal('openModal');
        }
    };
});
