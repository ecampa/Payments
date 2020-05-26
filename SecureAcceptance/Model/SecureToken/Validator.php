<?php

namespace Payments\SecureAcceptance\Model\SecureToken;


/**
 * Class Validator
 */
class Validator
{
    const TOKEN_VALIDITY_PERIOD = 600;
    const TOKEN_MAX_USAGES = 3;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dateTime = $dateTime;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    public function validate($token)
    {
        $tokenData = $this->checkoutSession->getSecureToken();

        if (!$tokenData) {
            return false;
        };

        if (!\Magento\Framework\Encryption\Helper\Security::compareStrings($tokenData['value'], $token)) {
            return false;
        }

        if ($this->dateTime->gmtTimestamp() - $tokenData['iat'] > static::TOKEN_VALIDITY_PERIOD) {
            return false;
        }

        $tokenData['usages']++;

        $this->checkoutSession->setSecureToken($tokenData);

        return $tokenData['usages'] <= static::TOKEN_MAX_USAGES;
    }
}
