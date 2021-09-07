<?php

namespace Payments\Core\Gateway\Request\Rest;

use Magento\Payment\Helper\Formatter;

class AddressDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds Address Data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $request['orderInformation']['billTo'] = $this->buildAddress($order->getBillingAddress());

        if ($order->getShippingAddress()) {
            $request['orderInformation']['shipTo'] = $this->buildAddress($order->getShippingAddress());
        }

        return $request;
    }

    private function buildAddress(\Magento\Payment\Gateway\Data\AddressAdapterInterface $address)
    {
        return [
            'firstName' => $address->getFirstname(),
            'middleName' => $address->getMiddlename(),
            'lastName' => $address->getLastname(),
            'company' => $address->getCompany(),
            'email' => $address->getEmail(),
            'address1' => $address->getStreetLine1(),
            'address2' => $address->getStreetLine2(),
            'locality' => $address->getCity(),
            'administrativeArea' => $address->getRegionCode(),
            'country' => $address->getCountryId(),
            'phoneNumber' => $address->getTelephone(),
            'postalCode' => $address->getPostcode(),
        ];
    }
}
