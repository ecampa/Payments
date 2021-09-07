<?php
namespace Payments\WeChatPay\Gateway\Validator;

use Payments\WeChatPay\Gateway\Config\Config;

class SaleResponseValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if ($this->isSuccessfulTransaction($validationSubject['response'])) {
            return $this->createResult(true, []);
        }

        return $this->createResult(false, [__('Gateway rejected the transaction.')]);
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isSuccessfulTransaction(array $response)
    {
        $apStatus = $response['apSaleReply']->paymentStatus ?? '';
        return $apStatus == \Payments\WeChatPay\Gateway\Config\Config::PAYMENT_STATUS_PENDING;
    }
}
