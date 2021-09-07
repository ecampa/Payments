<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;

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
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $request = $this->addKeyPrefix($this->buildAddress($order->getBillingAddress()), 'bill_to_');

        if ($order->getShippingAddress()) {
            $request = array_merge(
                $request,
                $this->addKeyPrefix($this->buildAddress($order->getShippingAddress()), 'ship_to_')
            );
        }

        return $request;
    }

    private function addKeyPrefix($inputDataArray, $prefix = '')
    {
        $out = [];

        foreach ($inputDataArray as $key => $item) {
            $out[$prefix . $key] = $item;
        }

        return $out;
    }

    private function buildAddress(\Magento\Payment\Gateway\Data\AddressAdapterInterface $address)
    {
        return [
            'forename' => $address->getFirstname(),
            'surname' => $address->getLastname(),
            'company_name' => $address->getCompany(),
            'email' => $address->getEmail(),
            'address_line1' => $address->getStreetLine1(),
            'address_line2' => $address->getStreetLine2(),
            'address_city' => $address->getCity(),
            'address_state' => $address->getRegionCode(),
            'address_country' => $address->getCountryId(),
            'address_postal_code' => $address->getPostcode(),
            'phone' => $address->getTelephone(),
        ];
    }
}
