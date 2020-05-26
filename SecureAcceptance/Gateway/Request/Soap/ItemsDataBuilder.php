<?php

namespace Payments\SecureAcceptance\Gateway\Request\Soap;

use Magento\Payment\Helper\Formatter;

class ItemsDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var string
     */
    private $objectName;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        string $objectName = null
    ) {
        $this->subjectReader = $subjectReader;
        $this->objectName = $objectName;
    }

    /**
     * Builds Order Data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        $result = $this->{'get' . ucfirst($this->objectName) . 'Items'}($payment);

        return ['item' => $result];
    }

    private function getOrderItems($payment)
    {
        return $this->getItems($payment->getOrder()->getAllItems());
    }

    private function getCreditmemoItems($payment)
    {
        return $this->getItems($payment->getCreditmemo()->getAllItems());
    }

    private function getInvoiceItems($payment)
    {
        $invoice = $payment->getInvoice();
        if (!$invoice) {
            $invoice = $payment->getCreatedInvoice();
        }
        return $this->getItems($invoice->getAllItems());
    }

    private function getItems($items)
    {
        $result = [];
        $i = 0;
        foreach ($items as $key => $item) {
            //getProductType used for order items, getOrderItem for invoice, creditmemo
            $type = $item->getProductType() ?: $item->getOrderItem()->getProductType();

            if ($item->getBasePrice() == 0) {
                continue;
            }

            if (in_array($type, [
                \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            ])) {
                continue;
            }

            //getQtyOrdered used for order items, getQty for invoice, creditmemo
            $qty = $item->getQty() ?: $item->getQtyOrdered();

            $price = $item->getBasePrice() - $item->getBaseDiscountAmount() / $qty;

            $result[$i] = [
                'id' => $i,
                'productName' => preg_replace("/[^a-zA-Z0-9\s]/", "", $item->getName()),
                'productSKU' => $item->getSku(),
                'productCode' => $type,
                'quantity' => (int)$qty,
                'unitPrice' => $this->formatPrice($price),
                'taxAmount' => $this->formatPrice($item->getBaseTaxAmount())
            ];
            $i++;
        }
        return $result;
    }
}
