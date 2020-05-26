<?php
namespace Payments\ThreeDSecure\Gateway\Request\Cca;

class TokenDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{


    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Framework\Math\Random $random
    ) {
        $this->subjectReader = $subjectReader;
        $this->random = $random;
    }

    /**
     * Builds JTW token data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $amount = $this->subjectReader->readAmount($buildSubject);

        $result = [
            'OrderDetails' => [
                'OrderNumber' => $order->getOrderIncrementId() ?? $this->random->getUniqueHash('order_'),
                'Amount' => round($amount * 100),
                'CurrencyCode' => $order->getCurrencyCode(),
                'OrderChannel' => 'S',
            ]
        ];

        $billingAddress = $order->getBillingAddress();

        $result['Consumer']['Email1'] = $billingAddress->getEmail();

        $result['Consumer']['BillingAddress'] = $this->buildAddress($billingAddress);

        if (!empty($buildSubject['cardBin'])) {
            $result['Consumer']['Account']['AccountNumber'] = $buildSubject['cardBin'];
        }

        if ($order->getShippingAddress()) {
            $result['Consumer']['ShippingAddress'] = $this->buildAddress($order->getShippingAddress());
        };

        return $result;
    }

    private function buildAddress(\Magento\Payment\Gateway\Data\AddressAdapterInterface $address)
    {
        return [
            'FirstName' => $address->getFirstname(),
            'LastName' => $address->getLastname(),
            'Address1' => $address->getStreetLine1(),
            'Address2' => $address->getStreetLine2(),
            'City' => $address->getCity(),
            'State' => $address->getRegionCode(),
            'CountryCode' => $address->getCountryId(),
            'Phone1' => $address->getTelephone(),
            'PostalCode' => $address->getPostcode(),
        ];
    }
}
