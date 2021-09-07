<?php
namespace Payments\WeChatPay\Plugin;

class SalesOrderInvoicePlugin
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $subject
     * @param $result
     * @return bool
     */
    public function afterCanCapture(
        \Magento\Sales\Model\Order\Invoice $subject,
        $result
    ) {
        $method = $subject->getOrder()->getPayment()->getMethod();
        if ($method == \Payments\WeChatPay\Model\Ui\ConfigProvider::CODE) {
            return false;
        }

        return $result;
    }
}
