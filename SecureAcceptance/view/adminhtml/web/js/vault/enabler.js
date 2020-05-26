define(
    [],
    function () {
        return function (element, gatewayCode) {
            return element.find(
                '[data-container="' + gatewayCode + '-vault-enabled"]'
            ).prop("checked");
        }
    }
);
