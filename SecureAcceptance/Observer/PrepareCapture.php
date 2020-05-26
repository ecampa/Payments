<?php
namespace Payments\SecureAcceptance\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PrepareCapture implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $payment = $observer->getPayment();
        $payment->setInvoice($observer->getInvoice());
    }
}
