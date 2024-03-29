define(
    [
        'jquery',
        'underscore',
        'Magento_Payment/js/view/payment/cc-form',
        'flex-microform',
        'Payments_SecureAcceptance/js/model/credit-card-type-map',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Vault/js/view/payment/vault-enabler',
        'Payments_SecureAcceptance/js/model/microform/error-processor',
        'Payments_SecureAcceptance/js/action/microform/get-token',
        'Payments_SecureAcceptance/js/model/credit-card-validation/microform-credit-card-validation'
    ]
    , function (
        $,
        _,
        Component,
        Flex,
        cardTypeMap,
        additionalValidators,
        VaultEnabler,
        errorProcessor,
        getTokenAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                template: 'Payments_SecureAcceptance/payment/flex-microform',
                code: 'payments_sa',
                imports: {
                    onActiveChange: 'active'
                }
            },
            microformResponse: {},
            microform: null,
            microformInitialized: false,
            containerSelector: '#flex-cc-number',
            labelSelector: '#cardNumber-label',
            containerSelectorCvn: '#flex-cc-cvn',
            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
            },
            formAfterRender: function () {
                $('#payments_sa_form').validation({
                    ignore: ""
                });
                this.flexInitForm();
            },
            flexInitForm: function () {
                if (this.microformInitialized) {
                    return;
                }

                this.microformInitialized = true;

                getTokenAction(this.messageContainer, this.getData()).then(function (token) {

                    try {
                        var flex = new Flex.Flex(token),
                            microform = flex.microform({styles: this.getStyles()}),
                            number = microform.createField(
                                'number',
                                {
                                    placeholder: '',
                                    autoformat: false
                                }
                            );

                        number.load(this.containerSelector);

                        number.on('change', this.cardTypeChangeHandler.bind(this));
                        number.on('change', this.validationChangeHandler.bind(this));
                        number.on('autocomplete', this.cardAutofillHandler.bind(this));

                        this.microform = microform;

                        if (!this.hasVerification()) {
                            return;
                        }

                        var securityCode = microform.createField(
                            'securityCode',
                            {
                                placeholder: '',
                                styles: this.getStyles()
                            }
                        );

                        securityCode.load(this.containerSelectorCvn);

                        securityCode.on('change', this.validationChangeHandlerCvn.bind(this));

                    } catch (e) {
                        console.log(e);
                    }

                }.bind(this));
            },
            initObservable: function () {
                this._super()
                    .observe(['active']);
                return this;
            },
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },
            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },
            getCode: function () {
                return this.code;
            },
            getData: function () {

                var data = {
                        'method': this.getCode(),
                        'additional_data': $.extend({}, {
                            'cvv': this.creditCardVerificationNumber(),
                            'expDate': this.padMonth(this.creditCardExpMonth()) + '-' + this.creditCardExpYear(),
                            'token': this.microformResponse,
                            'ccType': this.creditCardType()
                        })
                    }
                ;

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            },
            getStyles: function () {
                var textInput = $('<input type="text" class="input-text .abs-visually-hidden"/>')
                    , errorInput = $('<input type="text" class="input-text mage-error .abs-visually-hidden"/>')
                    , styles;

                $('#flex-cc-number').append(textInput).append(errorInput);

                styles = {
                    'input': {
                        'font-size': textInput.css('font-size'),
                        'font-family': textInput.css('font-family'),
                        'color': textInput.css('color')
                    },
                    ':disabled': {'cursor': 'not-allowed'},
                    'invalid': {'color': errorInput.css('color')}
                };

                textInput.remove();
                errorInput.remove();

                return styles;
            },
            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].vaultCode;
            },
            getAvailableCardTypes: function () {
                return window.checkoutConfig.payment[this.getCode()].availableCardTypes || '';
            },
            cardAutofillHandler: function (cardData) {

                if (!cardData) {
                    return;
                }

                if (cardData.expirationMonth && parseInt(cardData.expirationMonth, 10)) {
                    this.creditCardExpMonth(parseInt(cardData.expirationMonth, 10));
                }

                if (cardData.expirationYear) {
                    this.creditCardExpYear(cardData.expirationYear);
                }
            },
            cardTypeChangeHandler: function (event) {
                var card, magentoType;

                if (!event.card || event.card.length === 0) {
                    this.creditCardType(null);
                    this.selectedCardType(null);
                    return;
                }

                card = event.card[0];
                magentoType = cardTypeMap.getMagentoType(card.cybsCardType);

                this.creditCardType(magentoType);
                this.selectedCardType(magentoType);
            },
            validateCardType: function () {
                var
                    cardType = this.creditCardType(),
                    availableTypes = this.getAvailableCardTypes(),
                    isValid;

                isValid = cardType && availableTypes && availableTypes.split(',').indexOf(cardType) !== -1;

                $(this.containerSelector).attr('data-valid-type', isValid);
            },
            validationChangeHandler: function (data) {
                $(this.containerSelector).attr('data-valid', data.valid);
            },
            validationChangeHandlerCvn: function (data) {
                $(this.containerSelectorCvn).attr('data-valid', data.valid);
            },
            validate: function () {
                var form = $('#payments_sa_form'),
                    formValid
                ;

                this.validateCardType();

                formValid = form.validation && form.validation('isValid');
                return formValid;
            },
            padMonth: function (value) {
                if (parseInt(value, 10) < 10) {
                    return '0' + value;
                }

                return '' + value;
            },
            placeOrderContinue: function(data, event, _super) {
                _super(data, event);
            },
            placeOrder: function (data, event) {

                var _super = this._super.bind(this);

                if (!this.validate() || !additionalValidators.validate()) {
                    return;
                }

                this.isPlaceOrderActionAllowed(false);

                this.microform.createToken(
                    {
                        cardExpirationMonth: this.padMonth(this.creditCardExpMonth()),
                        cardExpirationYear: this.creditCardExpYear()
                    },
                    function (err, response) {
                        this.isPlaceOrderActionAllowed(true);

                        if (err) {
                            errorProcessor.process(err, this.messageContainer);
                            return;
                        }
                        this.microformResponse = response;
                        this.placeOrderContinue(data, event, _super);
                    }.bind(this)
                );
            }
        });
    });
