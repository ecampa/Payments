<?php

namespace Payments\SecureAcceptance\Plugin\Helper;

class RequestDataBuilderPlugin
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * RequestDataBuilderPlugin constructor.
     *
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Session\SessionManagerInterface $checkoutSession
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->encryptor = $encryptor;
    }

    /**
     *
     *
     * Appends override_custom_receipt_page field to the request and generates a new signature.
     * Erases device fingerprint and remote ip.
     *
     * @param \Payments\SecureAcceptance\Helper\RequestDataBuilder $subject
     * @param $result
     * @return mixed
     */
    public function afterBuildSilentRequestData(
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $subject,
        $result
    ) {

        $result['override_custom_receipt_page'] = $this->getCustomReceiptPageUrl();

        if ($this->config->isSilent()) {
            $result[\Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_SID] = $this->encryptor->encrypt($this->checkoutSession->getSessionId());
        }

        unset($result['device_fingerprint_id']);
        unset($result['customer_ip_address']);
        unset($result['signed_field_names']);
        unset($result['signature']);
        $result['access_key'] = $this->getSopAccessKey();
        $result['profile_id'] = $this->getSopProfileId();
        $result['signed_field_names'] = $subject->getSignedFields($result);
        $result['signature'] = $subject->sign($result, $this->getSopSecretKey());

        return $result;
    }

    /**
     *
     *
     * Appends override_custom_receipt_page field to the request and generates a new signature.
     *
     * @param \Payments\SecureAcceptance\Helper\RequestDataBuilder $subject
     * @param $result
     * @return mixed
     */
    public function afterBuildRequestData(
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $subject,
        $result
    ) {

        $result['override_custom_receipt_page'] = $this->getCustomReceiptPageUrl();

        unset($result['signed_field_names']);
        unset($result['signature']);
        $result['access_key'] = $this->getAccessKey();
        $result['profile_id'] = $this->getProfileId();
        $result['signed_field_names'] = $subject->getSignedFields($result);
        $result['signature'] = $subject->sign($result, $this->getSecretKey());

        return $result;
    }

    private function getCustomReceiptPageUrl()
    {
        return $this->urlBuilder->getUrl('paymentssaadmin/transparent/response',
            ['_secure' => $this->request->isSecure()]);
    }

    /**
     * @return \Magento\Quote\Model\Quote\Interceptor
     */
    private function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    private function getSopProfileId()
    {
        return $this->config->getValue(\Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_PROFILE_ID,
            $this->getQuote()->getStoreId());
    }

    private function getSopAccessKey()
    {
        return $this->config->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_ACCESS_KEY,
            $this->getQuote()->getStoreId()
        );
    }

    private function getSopSecretKey()
    {
        return $this->config->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_SOP_SECRET_KEY,
            $this->getQuote()->getStoreId()
        );
    }

    private function getProfileId()
    {
        return $this->config->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_PROFILE_ID,
            $this->getQuote()->getStoreId()
        );
    }

    private function getAccessKey()
    {
        return $this->config->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_ACCESS_KEY,
            $this->getQuote()->getStoreId()
        );
    }

    private function getSecretKey()
    {
        return $this->config->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_SECRET_KEY,
            $this->getQuote()->getStoreId()
        );
    }
}
