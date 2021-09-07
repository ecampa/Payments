<?php
namespace Payments\Core\Gateway\Request\Soap;

use Magento\Payment\Helper\Formatter;

class AddressDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    private $remoteAddress;

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
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $request['billTo'] = $this->buildAddress($order->getBillingAddress());

        if ($order->getShippingAddress()) {
            $request['shipTo'] = $this->buildAddress($order->getShippingAddress());
        }

        return $request;
    }

    private function buildAddress(\Magento\Payment\Gateway\Data\AddressAdapterInterface $address)
    {
        return [
            'firstName' => $address->getFirstname(),
            'lastName' => $address->getLastname(),
            'company' => $address->getCompany(),
            'email' => $address->getEmail(),
            'street1' => $address->getStreetLine1(),
            'street2' => $address->getStreetLine2(),
            'city' => $address->getCity(),
            'state' => $address->getRegionCode(),
            'country' => $address->getCountryId(),
            'postalCode' => $address->getPostcode(),
            'phoneNumber' => $address->getTelephone(),
        ];
    }
}
