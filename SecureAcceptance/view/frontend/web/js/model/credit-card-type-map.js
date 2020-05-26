define([], function () {
    'use strict';

    var types = [
        {
            gatewayCardType: '001',
            type: 'VI'
        },
        {
            gatewayCardType: '002',
            type: 'MC'
        },
        {
            gatewayCardType: '003',
            type: 'AE'
        },
        {
            gatewayCardType: '004',
            type: 'DI'
        },
        {
            gatewayCardType: '005',
            type: 'DN'
        },
        {
            gatewayCardType: '007',
            type: 'JCB'
        },
        {
            gatewayCardType: '042',
            type: 'MI'
        }
    ];

    return {
        getMagentoType: function (gatewayCardType) {
            var i, value;

            if (!gatewayCardType) {
                return null;
            }

            for (i = 0; i < types.length; i++) {
                value = types[i];

                if (value.gatewayCardType === gatewayCardType) {
                    return value.type;
                }
            }
        }
    };

});
