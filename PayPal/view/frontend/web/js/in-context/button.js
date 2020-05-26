define(
    [
        'uiComponent',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        $
    ) {
        'use strict';

        return Component.extend({

            defaults: {},

            
            initialize: function () {
                this._super();

                return this.initEvents();
            },

            
            initEvents: function () {
                $('a[data-action="' + this.linkDataAction + '"]').off('click.' + this.id)
                    .on('click.' + this.id, this.click.bind(this));

                return this;
            },

            
            click: function (event) {
                event.preventDefault();

                $('#' + this.paypalButton).click();
            }
        });
    }
);
