define([
        'jquery',
        'transparent',
        'Magento_Ui/js/modal/modal'
    ],
    function ($) {
        $.widget('payments.transparent_iframe', $.mage.transparent, {
            _iFrameContainer: null,
            _preparePaymentData: function (data) {
                return data;
            },
            _create: function () {
                this._super();

                this._iFrameContainer = $('#' + this.options.gateway + '-transparent-iframe-container').modal({
                    autoOpen: false,
                    buttons: [],
                    closed: this._closeIframe.bind(this),
                    clickableOverlay: false
                })

            },
            _postPaymentToGateway: function (response) {
                var $iframeSelector = $('[data-container="' + this.options.gateway + '-transparent-iframe"]'),
                    that = this,
                    data,
                    tmpl,
                    iframe,
                    $form
                ;

                data = response;
                tmpl = this.hiddenFormTmpl({
                    data: {
                        target: $iframeSelector.attr('name'),
                        action: this.options.cgiUrl,
                        inputs: data
                    }
                });

                iframe = $iframeSelector
                    .on('submit', function (event) {
                        event.stopPropagation();
                        iframe.appendTo(this._iFrameContainer);
                        this._iFrameContainer.modal('openModal');
                    }.bind(this));

                iframe.show();

                iframe.off('load').on('load', this._iframeLoadHandler.bind(this));

                $form = $(tmpl).appendTo(iframe);
                $form.submit();
                iframe.html('');
            },
            _iframeLoadHandler: function (event) {
                var iframe = event.target,
                    iframeLocation
                ;
                if (!this.options.context) {
                    return;
                }

                try {
                   
                    iframeLocation = iframe.contentWindow.location.href;
                    if (iframeLocation === 'about:blank') {
                        return;
                    }

                } catch (e) {
                    this.options.context.iframeLoadHandler.bind(this.options.context)();
                    return;
                }

               
                this._iFrameContainer.modal('closeModal');
                this.options.context.iframeReturnHandler.bind(this.options.context)();

            },
            _closeIframe: function () {
                this._iFrameContainer.find('iframe').html('');
                $('[data-container="' + this.options.gateway + '-transparent-iframe"]').off('submit');
                if (this.options.context) {
                    this.options.context.iframeCloseHandler.bind(this.options.context)();
                }

            }
        });

        return $.payments.transparent_iframe;
    });
