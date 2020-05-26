<?php
namespace Payments\SecureAcceptance\Model;


interface SignatureManagementInterface
{

    /**
     * Generates signature string for Secure Acceptance SOP and WM requests
     *
     * @param $params
     * @param $secretKey
     *
     * @return string
     */
    public function sign($params, $secretKey);

    /**
     * Validates signature for Secure Acceptance SOP and WM requests
     *
     * @param $response
     * @param $key
     *
     * @return bool
     */
    public function validateSignature($response, $key);

}
