define(
    [
        'Magento_Checkout/js/view/payment/default',
        'uiRegistry',
        'mage/translate',
        'jquery'
    ],
    function (Component, registry, $t, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Payments_ECheck/payment/form',
                code: 'payments_echeck',
                active: false,
                checkBankTransitNumber: '',
                checkNumber: '',
                checkAccountNumber: '',
                driversLicenseNumber: ''
            },

            initObservable: function () {

                this._super().observe([
                    'active',
                    'checkBankTransitNumber',
                    'checkNumber',
                    'checkAccountNumber',
                    'driversLicenseNumber'
                ]);
                return this;
            },

            getCode: function () {
                return this.code;
            },

            getTitle: function () {

              return window.checkoutConfig.payment[this.getCode()].title;
            },

            
            isActive: function () {
                var active = (this.getCode() === this.isChecked());

                this.active(active);

                return active;
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'check_bank_transit_number': this.checkBankTransitNumber(),
                        'check_number': this.checkNumber(),
                        'check_account_number': this.checkAccountNumber(),
                        'drivers_license_number': this.driversLicenseNumber(),
                        'drivers_license_country': registry.get("checkoutProvider").echeckDriversLicense.country_id,
                        'drivers_license_state': registry.get("checkoutProvider").echeckDriversLicense.region_id
                    }
                };
            },

            
            getECheckImageUrl: function () {
                return window.checkoutConfig.payment[this.getCode()].echeckImage;
            },

            
            getECheckImageHtml: function () {
                return '<img src="' + this.getECheckImageUrl() +
                    '" alt="' + $t('Check Visual Reference') +
                    '" title="' + $t('Check Visual Reference') +
                    '" />';
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            isDriversLicenseNumberRequired: function () {
                return !!parseInt(window.checkoutConfig.payment[this.getCode()].isDriversLicenseNumberRequired);
            },

            isCheckNumberRequired: function () {
                return !!parseInt(window.checkoutConfig.payment[this.getCode()].isCheckNumberRequired);
            }
        });
    }
);
