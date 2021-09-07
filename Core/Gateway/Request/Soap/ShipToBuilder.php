<?php
namespace Payments\Core\Gateway\Request\Soap;

class ShipToBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $order = $this->subjectReader->readPayment($buildSubject)->getOrder();

        if (!$order->getShippingAddress()) {
            return [];
        }

        return [
            'shipTo' => [
                'firstName' => $order->getShippingAddress()->getFirstname(),
                'lastName' => $order->getShippingAddress()->getLastname(),
                'company' =>  $order->getShippingAddress()->getCompany(),
                'email' =>  $order->getShippingAddress()->getEmail(),
                'street1' =>  $order->getShippingAddress()->getStreetLine1(),
                'street2' =>  $order->getShippingAddress()->getStreetLine2(),
                'city' =>  $order->getShippingAddress()->getCity(),
                'state' =>  $order->getShippingAddress()->getRegionCode(),
                'country' =>  $order->getShippingAddress()->getCountryId(),
                'phoneNumber' =>  $order->getShippingAddress()->getTelephone(),
                'postalCode' =>  $order->getShippingAddress()->getPostcode()
            ]
        ];
    }
}
