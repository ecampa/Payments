<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;

use Payments\SecureAcceptance\Helper\RequestDataBuilder;

class CreateTokenRequest implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const TYPE_CREATE_TOKEN = 'create_payment_token';

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $gatewayConfig;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $gatewayConfig,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Math\Random $random,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->subjectReader = $subjectReader;
        $this->gatewayConfig = $gatewayConfig;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->localeResolver = $localeResolver;
        $this->urlBuilder = $urlBuilder;
        $this->dateTime = $dateTime;
        $this->random = $random;
        $this->checkoutSession = $checkoutSession;
        $this->encryptor = $encryptor;
    }

    /**
     * Builds create token request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $cardType = $buildSubject['card_type'] ?? false;
        $agreementIds = $buildSubject['agreementIds'] ?? false;

        $order = $paymentDO->getOrder();

        $data = [];
        $data['access_key'] = $this->getAccessKey();
        $data['profile_id'] = $this->getProfileId();
        $data['transaction_uuid'] = $this->random->getUniqueHash();

        if ($unsignedFieldNames = $this->getUnsignedFieldNames()) {
            $data['unsigned_field_names'] = $unsignedFieldNames;
        }

        $data['locale'] = $this->requestDataBuilder->getLocale();
        $data['transaction_type'] = self::TYPE_CREATE_TOKEN;
        $data['reference_number'] = 'token_request_' . $order->getId();
        $data[\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_QUOTE_ID] = $order->getId();

        if ($this->gatewayConfig->isSilent()) {
            $data[\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_SID] = $this->encryptor->encrypt($this->checkoutSession->getSessionId());
        }

        $data['amount'] = '0.00';
        $data['currency'] = $order->getCurrencyCode();
        $data['payment_method'] = 'card';

        $billingAddress = $order->getBillingAddress();

        $data['bill_to_forename'] = $billingAddress->getFirstname();
        $data['bill_to_surname'] = $billingAddress->getLastname();
        $data['bill_to_email'] = $billingAddress->getEmail();
        $data['bill_to_address_country'] = $billingAddress->getCountryId();
        $data['bill_to_address_city'] = $billingAddress->getCity();
        $data['bill_to_address_state'] = $billingAddress->getRegionCode();
        $data['bill_to_address_line1'] = $billingAddress->getStreetLine1();
        if ($streetLine2 = $billingAddress->getStreetLine2()) {
            $data['bill_to_address_line2'] = $streetLine2;
        }
        $data['bill_to_address_postal_code'] = $billingAddress->getPostcode();

        $data['skip_decision_manager'] = $this->gatewayConfig->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_TOKEN_SKIP_DM
        ) ? 'true' : 'false';

        $data['skip_auto_auth'] = $this->gatewayConfig->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_TOKEN_SKIP_AUTO_AUTH
        ) ? 'true' : 'false';

        if (!empty($agreementIds)) {
            $data[\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_AGREEMENT_IDS] = implode(',', $agreementIds);
        }

        $data['override_custom_receipt_page'] = $this->urlBuilder->getUrl(
            'paymentssa/index/placeorder',
            ['_secure' => true]
        );

        if ($fingerPrintId = $this->checkoutSession->getFingerprintId()) {
            $data['device_fingerprint_id'] = $fingerPrintId;
        }

        $data['signed_date_time'] = $this->dateTime->gmtDate("Y-m-d\\TH:i:s\\Z");
        $data = $this->filterEmptyValues($data);

        $data['signed_field_names'] = $this->requestDataBuilder->getSignedFields($data);

        $data['signature'] = $this->requestDataBuilder->sign($data, $this->getSecretKey());
        $data['card_type'] = $this->requestDataBuilder->getCardType($cardType);

        if ($data['card_type'] == 'undefined') {
            unset($data['card_type']);
        }

        return $data;
    }

    private function filterEmptyValues($data)
    {
        return array_filter($data, function ($value) {
            return !empty($value);
        });
    }

    private function getAccessKey()
    {
        if ($this->gatewayConfig->isSilent()) {
            return $this->gatewayConfig->getSopAccessKey();
        }
        return $this->gatewayConfig->getAuthAccessKey();
    }

    private function getProfileId()
    {
        if ($this->gatewayConfig->isSilent()) {
            return $this->gatewayConfig->getSopProfileId();
        }
        return $this->gatewayConfig->getAuthProfileId();
    }

    private function getSecretKey()
    {
        if ($this->gatewayConfig->isSilent()) {
            return $this->gatewayConfig->getSopSecretKey();
        }
        return $this->gatewayConfig->getAuthSecretKey();
    }

    private function getUnsignedFieldNames()
    {
        if ($this->gatewayConfig->isSilent()) {
            $fieldNames = ['card_type','card_number','card_expiry_date'];

            if (!$this->gatewayConfig->getIgnoreCvn()) {
                $fieldNames[] = 'card_cvn';
            }

            return implode(',', $fieldNames);
        }

        return null;
    }
}
