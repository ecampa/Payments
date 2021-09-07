<?php
namespace Payments\WeChatPay\Model;

use Payments\WeChatPay\Gateway\Config\Config;

class StatusCheckMessageMapper
{
    private $messageMap = [
        \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_ABANDONED => 'Payment not processed due to user inactivity. Please try again or select an alternate payment method.',
        \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_FAILED => 'Your payment could not be processed and you have not been charged. You will now be redirected to your shopping cart.',
        \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_PENDING => 'Your payment is being processed. Please do not close or refresh the browser window.',
        \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_SETTLED => 'Your payment was successfully processed. Please wait while we redirect you to your order confirmation.',
    ];

    public function getMessage($wcpStatus)
    {
        return __($this->messageMap[$wcpStatus] ?? 'Something wrong.');
    }
}
