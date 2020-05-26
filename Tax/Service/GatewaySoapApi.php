<?php
namespace Payments\Tax\Service;

use Payments\Core\Helper\RequestDataBuilder;
use Payments\Core\Model\Config;
use Payments\Core\Service\AbstractConnection;
use Payments\Tax\Model\Config as TaxConfig;
use Magento\Framework\App\ProductMetadata;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Api\Data\TaxClassInterface;
use Payments\Core\Model\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

class GatewaySoapApi extends \Payments\Core\Service\AbstractConnection
{
    const SUCCESS_REASON_CODE = 100;

    /**
     * @var \SoapClient
     */
    public $client;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session $session
     */
    private $session;

    /**
     * @var Config
     */
    private $gatewayConfig;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    private $productMetadata;

    /**
     * @var \Magento\Tax\Helper\Data $taxData
     */
    private $taxData;

    /**
     * @var \Magento\Tax\Api\TaxClassRepositoryInterface
     */
    private $taxClassRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \stdClass
     */
    private $response;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $gatewayConfig
     * @param TaxConfig $taxConfig
     * @param LoggerInterface $logger
     * @param RequestDataBuilder $requestDataHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param ProductMetadata $productMetadata
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepositoryInterface
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \SoapClient|null $client
     * @throws \Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\Config $gatewayConfig,
        \Payments\Tax\Model\Config $taxConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\Core\Helper\RequestDataBuilder $requestDataHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        ProductMetadata $productMetadata,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \SoapClient $client = null
    ) {
        parent::__construct($scopeConfig, $logger);

        /**
         * Added soap client as parameter to be able to mock in unit tests.
         */
        if ($client !== null) {
            $this->setSoapClient($client);
        }

        $this->gatewayConfig = $gatewayConfig;
        $this->taxConfig = $taxConfig;

        $this->client = $this->getSoapClient();
        $this->requestDataHelper = $requestDataHelper;
        $this->session = $authSession;
        $this->productMetadata = $productMetadata;
        $this->taxData = $taxData;
        $this->taxClassRepository = $taxClassRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
    }

    /**
     * Tax calculation for order
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @return $this
     */
    public function getTaxForOrder(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $billingAddress = $quote->getBillingAddress();

        if (!$address->getPostcode()) {
            return $this;
        }

        $request = new \stdClass();
        $request->merchantID = $this->gatewayConfig->getMerchantId();
        $request->partnerSolutionID = \Payments\Core\Helper\RequestDataBuilder::PARTNER_SOLUTION_ID;
        if ($developerId = $this->gatewayConfig->getDeveloperId()) {
            $request->developerId = $developerId;
        }

        $request->merchantReferenceCode = uniqid('tax_request_' . $quote->getId() . '_');

        /**
         * Try to add the billingAddress from customer to billTo, when it's not available, use the store address
         * as billing address, since the tax is calculated based on store address (shipFrom)
         */
        $builtBillingAddress = $this->buildAddressForTax($billingAddress);
        $request->billTo = ($builtBillingAddress !== null) ? $builtBillingAddress : $this->buildAddressForTax($address);
        $request->shipTo = $this->buildAddressForTax($address);

        $purchaseTotals = new \stdClass();
        $purchaseTotals->currency = $quote->getQuoteCurrencyCode();
        $request->purchaseTotals = $purchaseTotals;

        $taxService = new \stdClass();

        $shippingCountry = $address->getCountryId();
        if ($shippingCountry == 'CA' || $shippingCountry == 'US') {
            $request->shipFrom = $this->buildStoreShippingFromForTax();
            $taxService = $this->buildTaxOrderConfigurationForTax($taxService);
        }

        $taxService->run = 'true';

        $nexusRegions = $this->taxConfig->getTaxNexusRegions(" ");
        if (!empty($nexusRegions)) {
            $taxService->nexus = $nexusRegions;
        }

        if ($shippingCountry != 'US') {
            $taxService->sellerRegistration = $this->taxConfig->getTaxMerchantVat();
            if ($address->getVatId() != null) {
                $taxService->buyerRegistration = $address->getVatId();
            }
        }

        $request->taxService = $taxService;

        if (! $items = $this->buildItemNodeFromShippingItems($quote, $quoteTaxDetails)) {
            return $this;
        }

        $request->item = $items;

        if ($this->orderChanged($request)) {
            $this->placeRequest($request);
        } else {
            $sessionResponse = $this->getSessionData('response');

            if (isset($sessionResponse)) {
                $this->response = $sessionResponse;
            }
        }

        return $this;
    }

    private function placeRequest($request)
    {
        $this->setSessionData('request', $this->serializer->serialize($request));

        try {
            $isValidShipToAddress = $this->validateAddress($request->shipTo);
            if ($isValidShipToAddress) {
                $this->logger->debug([__METHOD__ => (array) $request]);
                $response = $this->client->runTransaction($request);
                $this->logger->debug([__METHOD__ => (array) $response]);

                $this->response = $this->serializer->serialize($response);
                $this->setSessionData('response', $this->serializer->serialize($response));
            } else {
                $this->logger->error("Tax: unable to request. Missing shipTo information");
                $this->response = null;
                $this->unsetSessionData('response');
            }
        } catch (\Exception $e) {
            $this->response = null;
            $this->unsetSessionData('response');
            $this->logger->error("Tax: " . $e->getMessage());
        }
    }

    /**
     * Validate response
     *
     * @return bool
     */
    public function isValidResponse()
    {
        $response = $this->serializer->unserialize($this->response);

        if (!$response) {
            return false;
        }

        if ($response->reasonCode == self::SUCCESS_REASON_CODE && property_exists($response, 'taxReply')) {
            return true;
        }

        return false;
    }

    /**
     * Verify if request is different than the last one
     *
     * @param \stdClass $request
     * @return bool
     */
    private function orderChanged($request)
    {
        $sessionRequest = $this->getSessionData('request');

        if ($sessionRequest) {
            $unserializedSessionRequest = $this->serializer->unserialize($sessionRequest);

            if (!$unserializedSessionRequest) {
                return false;
            }

            $this->logger->debug("Tax: comparing session request objects to see if we should re-request taxes");

            if ($this->serializer->serialize($unserializedSessionRequest->item) != $this->serializer->serialize($request->item)) {
                $this->logger->debug("Tax: items have changed so requesting taxes again");
                return true;
            }
            if ($this->serializer->serialize($unserializedSessionRequest->shipTo) != $this->serializer->serialize($request->shipTo)) {
                $this->logger->debug("Tax: shipping addresses have changed so requesting taxes again");
                return true;
            }
            return false;

        } else {
            return true;
        }
    }

    /**
     * Get prefixed session data from checkout/session
     *
     * @param string $key
     * @return object
     */
    public function getSessionData($key)
    {
        return $this->checkoutSession->getData('payments_tax_' . $key);
    }

    /**
     * Set prefixed session data in checkout/session
     *
     * @param string $key
     * @param string $val
     * @return object
     */
    private function setSessionData($key, $val)
    {
        return $this->checkoutSession->setData('payments_tax_' . $key, $val);
    }

    /**
     * Unset prefixed session data in checkout/session
     *
     * @param string $key
     * @return object
     */
    private function unsetSessionData($key)
    {
        return $this->checkoutSession->unsetData('payments_tax_' . $key);
    }

    /**
     * Get item based on the unit price
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject
     * @return array
     */
    public function getItemFromResponse(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject)
    {
        if ($this->response !== null && $this->response !== '') {
            $response = $this->serializer->unserialize($this->response);

            if (!$response || !property_exists($response, 'taxReply') || !property_exists($response->taxReply, 'item')) {
                return null;
            }

            $items = $response->taxReply->item;

            if (is_array($items)) {
                foreach ($items as $item) {
                    if (property_exists($item, 'taxableAmount')) {
                        $unitPrice = $this->getPriceConsideringDiscount($itemDataObject);
                        $linePrice = $unitPrice * $itemDataObject->getQuantity();

                        if ($item->taxableAmount === $this->requestDataHelper->formatAmount($linePrice)) {
                            return (array)$item;
                        }
                    }
                }
            }

            if (is_object($items)) {
                return (array) $items;
            }
        }
    }

    /**
     * Get unit price considering discount
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject
     * @return float
     */
    private function getPriceConsideringDiscount(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject)
    {
        $discountAmount = $itemDataObject->getDiscountAmount();
        $itemUnitPrice = $itemDataObject->getUnitPrice();
        $unitPrice = $itemUnitPrice;

        if ($discountAmount != null && $discountAmount > 0) {
            $unitPrice = $itemUnitPrice - ($discountAmount / $itemDataObject->getQuantity());
        }

        return $this->requestDataHelper->formatAmount($unitPrice);
    }

    /**
     * Build order items
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     * @return array
     */
    private function buildItemNodeFromShippingItems(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
    ) {
        $lineItems = [];
        $store = $quote->getStore();
        $items = $quoteTaxDetails->getItems();

        $shippingPriceIncludeTax = (bool) $this->config->getValue(
            'tax/calculation/shipping_includes_tax',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        $itemId = 0;

        if (!empty($items)) {
            $parentQuantities = [];

            foreach ($items as $i => $item) {
                if ($item->getType() == 'product') {
                    $lineItem = new \stdClass();
                    $id = $i;
                    $parentId = $item->getParentCode();
                    $quantity = (int) $item->getQuantity();
                    $unitPrice = (float) $item->getUnitPrice();
                    $discount = (float) $item->getDiscountAmount() / $quantity;
                    $extensionAttributes = $item->getExtensionAttributes();
                    $sku = $extensionAttributes->__toArray()['sku'];
                    $productName = $extensionAttributes->__toArray()['product_name'];

                    if ($extensionAttributes->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                        $parentQuantities[$id] = $quantity;

                        if ($extensionAttributes->getPriceType() ==
                            \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC
                        ) {
                            continue;
                        }
                    }

                    if (isset($parentQuantities[$parentId])) {
                        $quantity *= $parentQuantities[$parentId];
                    }

                    if (!$this->taxData->applyTaxAfterDiscount($store)) {
                        $discount = 0;
                    }

                    if ($item->getTaxClassKey()->getValue()) {
                        $taxClass = $this->taxClassRepository->get($item->getTaxClassKey()->getValue());
                        $taxCode = $taxClass->getClassName();
                    } else {
                        $taxCode = \Payments\Tax\Model\Config::TAX_DEFAULT_CODE;
                    }

                    if ($this->productMetadata->getEdition() == 'Enterprise' &&
                        $extensionAttributes->getProductType() ==
                        \Magento\GiftCard\Model\Catalog\Product\Type\Giftcard::TYPE_GIFTCARD
                    ) {
                        $giftTaxClassId = $this->config->getValue('tax/classes/wrapping_tax_class');

                        if ($giftTaxClassId) {
                            $giftTaxClass = $this->taxClassRepository->get($giftTaxClassId);
                            $giftTaxClassCode = $giftTaxClass->getClassName();
                            $taxCode = $giftTaxClassCode;
                        } else {
                            $taxCode = \Payments\Tax\Model\Config::TAX_DEFAULT_CODE;
                        }
                    }

                    $lineItem->id = $id;
                    $lineItem->unitPrice = $this->requestDataHelper->formatAmount($unitPrice - $discount);

                    if ($lineItem->unitPrice <= 0) {
                        continue;
                    }

                    $lineItem->quantity = (string) $quantity;
                    $lineItem->productCode = $taxCode;
                    $lineItem->productName = $productName;
                    $lineItem->productSKU = $sku;

                    $lineItems[] = $lineItem;
                }

                $itemId++;
            }

            if (!$shippingPriceIncludeTax) {
                $shippingTaxClassId = $this->config->getValue(
                    'tax/classes/shipping_tax_class',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                );

                if (!empty($shippingTaxClassId)) {
                    /** @var TaxClassInterface $shippingTaxClass */
                    $shippingTaxClass = $this->taxClassRepository->get($shippingTaxClassId);
                    $lineItem = new \stdClass();
                    $lineItem->id = $itemId;
                    $lineItem->unitPrice = $this->requestDataHelper->formatAmount(
                        $quote->getShippingAddress()->getShippingAmount()
                    );
                    $lineItem->quantity = "1";
                    $lineItem->productCode = $shippingTaxClass->getClassName();
                    $lineItem->productName = 'shipping';
                    $lineItem->productSKU = 'SHIP' . $itemId;

                    $lineItems[] = $lineItem;
                }
            }
        }

        return $lineItems;
    }

    /**
     * @param Address $address
     * @return \stdClass $builtAddress
     */
    private function buildAddressForTax(\Magento\Quote\Model\Quote\Address $address)
    {
        $builtAddress = new \stdClass();

        if ($address->getCountry() !== null) {
            if ($address->getCountry() == 'CA' || $address->getCountry() == 'US') {
                $builtAddress->state = $address->getRegionCode();
            } else {
                $builtAddress->state = $address->getRegion();
            }
        }

        if ($address->getData(Address::KEY_POSTCODE) !== null) {
            $builtAddress->postalCode = $address->getPostcode();
        }

        if ($address->getData(Address::KEY_FIRSTNAME) !== null) {
            $builtAddress->firstName = $address->getFirstname();
        }

        if ($address->getData(Address::KEY_LASTNAME) !== null) {
            $builtAddress->lastName = $address->getLastname();
        }

        if ($address->getData(Address::KEY_STREET) !== null) {
            $builtAddress->street1 = $address->getStreetLine(1);
            $addressLine2 = $address->getStreetLine(2);
            if ($addressLine2 !== '' && $addressLine2 !== null && $addressLine2 !== $address->getStreetLine(1)) {
                $builtAddress->street2 = $addressLine2;
            }
        }

        if ($address->getData(Address::KEY_CITY) !== null) {
            $builtAddress->city = $address->getCity();
        }

        if ($address->getData(Address::KEY_EMAIL) !== null) {
            $builtAddress->email = $address->getEmail();
        }

        if ($address->getData(Address::KEY_COUNTRY_ID) !== null) {
            $builtAddress->country = $address->getCountryId();
        }

        if ($this->validateAddress($builtAddress)) {
            return $builtAddress;
        }

        return null;
    }

    /**
     * Retrieve Tax Shipping From configuration
     *
     * @return \stdClass
     */
    private function buildStoreShippingFromForTax()
    {
        $shipFrom = new \stdClass();
        $shipFrom->city = $this->taxConfig->getTaxShipFromCity();
        $shipFrom->country = $this->taxConfig->getTaxShipFromCountry();
        $shipFrom->state = $this->taxConfig->getTaxShipFromRegion();
        $shipFrom->postalCode = $this->taxConfig->getTaxShipFromPostcode();

        return $shipFrom;
    }

    /**
     * Build TaxService order node
     *
     * @param \stdClass $taxService
     * @return \stdClass
     */
    private function buildTaxOrderConfigurationForTax(\stdClass $taxService)
    {
        // orderAcceptance
        $taxService->orderAcceptanceCity = $this->taxConfig->getTaxAcceptanceCity();
        $taxService->orderAcceptanceCountry = $this->taxConfig->getTaxAcceptanceCountry();
        $taxService->orderAcceptanceState = $this->taxConfig->getTaxAcceptanceRegion();
        $taxService->orderAcceptancePostalCode = $this->taxConfig->getTaxAcceptancePostcode();

        // orderOrigin
        $taxService->orderOriginCity = $this->taxConfig->getTaxOriginCity();
        $taxService->orderOriginCountry = $this->taxConfig->getTaxOriginCountry();
        $taxService->orderOriginState = $this->taxConfig->getTaxOriginRegion();
        $taxService->orderOriginPostalCode = $this->taxConfig->getTaxOriginPostcode();

        return $taxService;
    }

    /**
     * @param $address
     * @return bool
     */
    public function validateAddress($address)
    {
        if ($address === null) {
            return false;
        }
        $validationKeys = ['city', 'state', 'postalCode', 'country'];

        foreach ($validationKeys as $key) {
            if ((empty($address->{$key}) || $address->{$key} == null)) {
                return false;
            }
        }

        return true;
    }
}
