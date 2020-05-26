<?php

namespace Payments\Atp\Model\Request;

use Payments\Atp\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class DmeAddressValidationDataBuilder extends \Payments\Atp\Model\Request\DmeValidationDataBuilder
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param Session $checkoutSession
     * @param Config $config
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        Session $checkoutSession,
        \Payments\Atp\Model\Config $config,
        CustomerRepository $customerRepository
    ) {
        parent::__construct($checkoutSession, $config);

        $this->customerRepository = $customerRepository;
    }

    /**
     * @param string $type
     * @param \Magento\Customer\Api\Data\AddressInterface $addressData
     * @return \stdClass
     */
    public function build($type, $addressData)
    {
        $request = new \stdClass();
        $customer = $this->customerRepository->getById($addressData->getCustomerId());

        $request->customerID = $customer->getId();
        $request->customerFirstName = $customer->getFirstname();
        $request->customerLastName = $customer->getLastname();
        $request->merchantID = $this->config->getMerchantId();
        $request->merchantReferenceCode = $this->getReferenceCode($type, $customer);

        if ($addressData->isDefaultBilling() || $addressData->getId() === (int)$customer->getDefaultBilling()) {
            $request->billTo = $this->buildAddressObject($addressData, true);
        }

        if ($addressData->isDefaultShipping() || $addressData->getId() === (int)$customer->getDefaultShipping()) {
            $request->shipTo = $this->buildAddressObject($addressData);
        }

        $request->billTo = $request->billTo ?? new \stdClass();
        $request->billTo->email = $customer->getEmail();

        $request->dmeService = $this->buildDmeServiceObject($type);
        $request->deviceFingerprintID = $this->getFingerprintId();

        return $request;
    }
}
