<?php
namespace Payments\SecureAcceptance\Gateway\Response;

use Payments\SecureAcceptance\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;

abstract class AbstractResponseHandler
{
    const TRANSACTION_ID = "transaction_id";
    const REASON_CODE = "reason_code";
    const DECISION = "decision";
    const MERCHANT_REFERENCE_CODE = "req_reference_number";
    const TOKEN_DATA = "token_data";
    const CARD_NUMBER = 'req_card_number';
    const REQUEST_ID = "requestID";

    const ADDITIONAL_INFO_REQUEST_KEYS = [
        'auth_amount',
        'auth_avs_code',
        'auth_avs_code_raw',
        'auth_code',
        'auth_response',
        'auth_time',
        'auth_trans_ref_no',
        'decision_case_priority',
        'decision_early_rcode',
        'decision_early_reason_code',
        'decision_early_return_code',
        'decision_rcode',
        'decision_reason_code',
        'decision_return_code',
        'decision_rflag',
        'decision_rmsg',
        'message',
        'request_token',
        'score_address_info',
        'score_bin_country',
        'score_card_account_type',
        'score_card_issuer',
        'score_card_scheme',
        'score_factors',
        'score_host_severity',
        'score_identity_info',
        'score_model_used',
        'score_phone_info',
        'score_rcode',
        'score_reason_code',
        'score_return_code',
        'score_rflag',
        'score_rmsg',
        'score_score_result',
        'score_suspicious_info',
        'score_time_local',
    ];

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * AbstractResponseHandler constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return \Magento\Payment\Model\InfoInterface
     */
    protected function getValidPaymentInstance(array $buildSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var \Magento\Payment\Model\InfoInterface $payment */
        $payment = $paymentDO->getPayment();

        ContextHelper::assertOrderPayment($payment);

        return $payment;
    }

    protected function handleAuthorizeResponse($payment, $response)
    {
        $tokenData = $this->buildTokenToSave($response);

        $payment->setTransactionId($response[self::TRANSACTION_ID]);
        $payment->setCcTransId($response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::TRANSACTION_ID, $response[self::TRANSACTION_ID]);
        $payment->setAdditionalInformation(self::REASON_CODE, $response[self::REASON_CODE]);
        $payment->setAdditionalInformation(self::DECISION, $response[self::DECISION]);
        $payment->setAdditionalInformation(self::MERCHANT_REFERENCE_CODE, $response[self::MERCHANT_REFERENCE_CODE]);

        $maskedPan = $response[self::CARD_NUMBER];
        $payment->setAdditionalInformation('cardNumber', substr($maskedPan, 0, 6) . str_repeat('x', strlen($maskedPan) - 10) . substr($maskedPan, -4));
        $payment->setCcLast4(substr($maskedPan, -4));
        $payment->setAdditionalInformation('cardType', $response['req_card_type']);

        //pass other returned fields by list
        foreach (self::ADDITIONAL_INFO_REQUEST_KEYS as $responseKey) {
            if (!isset($response[$responseKey])) {
                continue;
            }
            $payment->setAdditionalInformation($responseKey, $response[$responseKey]);
        }

        if ($response['reason_code'] === "480") {
            $payment->setIsFraudDetected(true);
            $payment->setIsTransactionPending(true);
        }

        if ($tokenData !== null) {
            $payment->setAdditionalInformation(self::TOKEN_DATA, $tokenData);
        }

        // validate that requested amount matches order amount to avoid replay attacks
        if ($payment->getBaseAmountOrdered() != $response['req_amount']) {
            $payment->setIsFraudDetected(true);
        }

        return $payment;
    }

    /**
     * @return array
     */
    private function buildTokenToSave($response)
    {
        /**
         * Avoid building because payment was placed with token or was set to REVIEW by DM
         */
        if (!isset($response['payment_token'])) {
            return null;
        }

        $cardType = isset($response['req_card_type']) ? $response['req_card_type'] : '';
        $cardNumber = $response['req_card_number'];
        $ccLastFour = substr($cardNumber, -4);
        $cardExpiry = isset($response['req_card_expiry_date']) ? $response['req_card_expiry_date'] : '';

        $result = [
            'payment_token' => $response['payment_token'],
            'card_type' => $cardType,
            'cc_last4' => $ccLastFour,
            'card_expiry_date' => $cardExpiry,
            'instrument_id' => $response['payment_token_instrument_identifier_id'] ?? null,
        ];

        if (preg_match('/^([0-9]{6}).+/', $cardNumber, $matches)) {
            $result['card_bin'] = $matches[1];
        }

        return $result;
    }
}
