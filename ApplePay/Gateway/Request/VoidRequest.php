<?php
namespace Payments\ApplePay\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

class VoidRequest extends \Payments\ApplePay\Gateway\Request\AbstractRequest implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $payment = $this->getValidPaymentInstance($buildSubject);

        /**
         * If order is under pending_payment it means that only the authorization was performed,
         * in this case we need to call an authorization reversal instead of void
         */
        if ($payment->getOrder()->getState() == Order::STATE_PENDING_PAYMENT) {
            $request = $this->requestDataBuilder->buildAuthorizeReversalRequestData($payment);
        } else {
            $request = $this->requestDataBuilder->buildVoidRequestData($payment);
        }

        return (array) $request;
    }
}
