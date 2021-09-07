<?php

namespace Payments\SecureAcceptance\Model\SecureToken;


use Payments\Core\Model\LoggerInterface;
use Payments\SecureAcceptance\Gateway\Config\Config;
use Magento\Quote\Model\Quote;

/**
 * Class Validator
 */
class Validator
{
    const TOKEN_MAX_USAGES = 3;

    const DEFAULT_TOKEN_VALIDITY_PERIOD = 600;


    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    private $tokenValidityPeriod;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->logger = $logger;
        $this->tokenValidityPeriod = $this->getTokenValidityPeriod();
    }

    /**
     * @param $token
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate($token)
    {
        $tokenData = $this->checkoutSession->getSecureToken();

        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();


        if (!$tokenData) {
            return false;
        }

        if (!\Magento\Framework\Encryption\Helper\Security::compareStrings($tokenData['value'], $token)) {
            $this->logger->error('Invalid CSRF Token for Quote ID: ' . $quote->getId());
            return false;
        }

        if ($tokenData['iat'] == null || $this->isTokenValidityPeriodExpired($tokenData['iat'])) {
            $this->logger->error('Token Validity Period Expired for Quote ID: ' . $quote->getId());
            return false;
        }

        $tokenData['usages']++;

        $this->checkoutSession->setSecureToken($tokenData);

        return $tokenData['usages'] <= static::TOKEN_MAX_USAGES;
    }

    private function getTokenValidityPeriod()
    {
        $seconds = $this->config->getCsrfTokenExpirationLifeTime();
        return ($seconds > 0 ? $seconds : self::DEFAULT_TOKEN_VALIDITY_PERIOD);
    }

    private function isTokenValidityPeriodExpired($tokenIssuedAt)
    {
        return ($this->dateTime->gmtTimestamp() - $tokenIssuedAt) > $this->tokenValidityPeriod;
    }
}
