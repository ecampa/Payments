<?php
namespace Payments\SecureAcceptance\Model;


class SignatureManagement implements \Payments\SecureAcceptance\Model\SignatureManagementInterface
{

    /**
     * @param $params
     * @return string
     */
    private function buildDataToSign($params)
    {
        $signedFieldNames = explode(",", $params['signed_field_names']);
        $dataToSign = [];
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . $params[$field];
        }
        return implode(",", $dataToSign);
    }

    /**
     * @inheritdoc
     */
    public function sign($params, $secretKey)
    {
        return base64_encode(hash_hmac('sha256', $this->buildDataToSign($params), $secretKey, true));
    }

    /**
     * @inheritdoc
     */
    public function validateSignature($response, $key)
    {
        if (!array_key_exists('signed_field_names', $response) || empty($response['signature'])) {
            return false;
        }
        $signature = $this->sign($response, $key);
        return hash_equals($signature, $response['signature']); // mitigating potential timing attack
    }
}
