<?php

namespace Payments\Tax\Model\Tax\Sales\Total\Quote;

use Payments\Tax\Model\Config;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    protected $gatewayApi;

    /**
     * @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory
     */
    protected $quoteExtensionFactory;

    /**
     * @var \Payments\Tax\Model\Tax\TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Tax constructor.
     *
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $quoteExtensionFactory
     * @param \Payments\Tax\Model\Tax\TaxCalculation $taxCalculation
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        \Payments\Tax\Service\GatewaySoapApi $gatewayApi,
        \Magento\Tax\Api\Data\QuoteDetailsItemExtensionFactory $quoteExtensionFactory,
        \Payments\Tax\Model\Tax\TaxCalculation $taxCalculation,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Payments\Tax\Model\Config $customTaxConfig
    ) {
        $this->gatewayApi = $gatewayApi;
        $this->quoteExtensionFactory = $quoteExtensionFactory;
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $customTaxConfig;
        $this->serializer = $serializer;

        parent::__construct(
            $taxConfig,
            $taxCalculationService,
            $quoteDetailsDataObjectFactory,
            $quoteDetailsItemDataObjectFactory,
            $taxClassKeyDataObjectFactory,
            $customerAddressFactory,
            $customerAddressRegionFactory,
            $taxData
        );
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        // normalizing address data
        $address = $shippingAssignment->getShipping()->getAddress();
        $region = $address->getData('region');
        $street = $address->getData('street');

        if (is_array($region)) {
            $address->setData('region', $region['region'] ? $region['region'] : '');
        }

        if (is_array($street)) {
            $address->setData('street', implode("\n", $street));
        }

        if ($this->isTaxApplicable($shippingAssignment)) {
            $baseQuoteTaxDetails = $this->getPreparedQuoteTaxDetails($shippingAssignment, $total, true);
            $this->gatewayApi->getTaxForOrder($quote, $baseQuoteTaxDetails, $shippingAssignment);
        }

        if ($this->gatewayApi->isValidResponse()) {
            $quoteTax = $this->getQuoteTax($quote, $shippingAssignment, $total);
            $this->applyTaxes($quoteTax, $shippingAssignment, $total);
        } else {
            return parent::collect($quote, $shippingAssignment, $total);
        }

        return $this;
    }

    private function applyTaxes($quoteTax, $shippingAssignment, $total)
    {
        $itemsByType = $this->organizeItemTaxDetailsByType($quoteTax['tax_details'], $quoteTax['base_tax_details']);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $response = $this->serializer->unserialize($this->gatewayApi->getSessionData('response'));

            foreach ($response->taxReply->item as $item) {
                if (!is_object($item) || !property_exists($item, 'taxableAmount')) {
                    continue;
                }

                if ($shippingTaxDetails->getPrice() > 0 &&
                    $shippingTaxDetails->getPrice() == $item->taxableAmount
                ) {
                    $shippingTaxDetails->setPriceInclTax($shippingTaxDetails->getPrice() + $item->totalTaxAmount);
                    $shippingTaxDetails->setPriceInclTax($shippingTaxDetails->getPrice() + $item->totalTaxAmount);
                    $shippingTaxDetails->setRowTotal($shippingTaxDetails->getPrice());
                    $shippingTaxDetails->setRowTax($item->totalTaxAmount);
                    $shippingTaxDetails->setRowTotalInclTax(
                        $shippingTaxDetails->getPrice() + $item->totalTaxAmount
                    );
                    $shippingTaxDetails->setTaxPercent(
                        round(100 * $item->totalTaxAmount / $item->taxableAmount, 4)
                    );

                    $baseShippingTaxDetails->setPriceInclTax(
                        $baseShippingTaxDetails->getPrice() + $item->totalTaxAmount
                    );
                    $baseShippingTaxDetails->setPriceInclTax(
                        $baseShippingTaxDetails->getPrice() + $item->totalTaxAmount
                    );
                    $baseShippingTaxDetails->setRowTotal($baseShippingTaxDetails->getPrice());
                    $baseShippingTaxDetails->setRowTax($item->totalTaxAmount);
                    $baseShippingTaxDetails->setRowTotalInclTax(
                        $baseShippingTaxDetails->getPrice() + $item->totalTaxAmount
                    );
                    $baseShippingTaxDetails->setTaxPercent(
                        round(100 * $item->totalTaxAmount / $item->taxableAmount, 4)
                    );
                }
            }
            $this->processShippingTaxInfo(
                $shippingAssignment,
                $total,
                $shippingTaxDetails,
                $baseShippingTaxDetails
            );
        }

        $this->processExtraTaxables($total, $itemsByType);
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }
    }

    protected function getQuoteTax(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $baseTaxDetailsInterface = $this->getPreparedQuoteTaxDetails($shippingAssignment, $total, true);
        $taxDetailsInterface = $this->getPreparedQuoteTaxDetails($shippingAssignment, $total, false);

        $baseTaxDetails = $this->calculateQuoteTaxDetails($quote, $baseTaxDetailsInterface, true);
        $taxDetails = $this->calculateQuoteTaxDetails($quote, $taxDetailsInterface, false);

        return [
            'base_tax_details' => $baseTaxDetails,
            'tax_details' => $taxDetails
        ];
    }

    protected function getPreparedQuoteTaxDetails($shippingAssignment, $total, $useBaseCurrency)
    {
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $priceIncludesTax = $this->_config->priceIncludesTax($shippingAddress->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
        if ($shippingDataObject != null) {
            $itemDataObjects[] = $shippingDataObject;
        }

        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemDataObjectFactory,
            $shippingAddress,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);

        return $quoteDetails;
    }

    public function calculateQuoteTaxDetails(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $taxDetails,
        $useBaseCurrency
    ) {
        $store = $quote->getStore();
        $taxDetails = $this->taxCalculation->customCalculateTax($taxDetails, $useBaseCurrency, $store);
        return $taxDetails;
    }

    public function mapItem(
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ) {
        $itemDataObject = parent::mapItem(
            $itemDataObjectFactory,
            $item,
            $priceIncludesTax,
            $useBaseCurrency,
            $parentCode
        );

        $lineItemTax = $this->gatewayApi->getItemFromResponse($itemDataObject);

        /**
         * @var \Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterface $extensionAttributes
         */
        $extensionAttributes = $itemDataObject->getExtensionAttributes()
            ? $itemDataObject->getExtensionAttributes()
            : $this->quoteExtensionFactory->create();

        $taxPercent = 0;
        if ($lineItemTax !== null &&
            array_key_exists('taxableAmount', $lineItemTax) && $lineItemTax['totalTaxAmount'] > 0
        ) {
            $taxPercent = round(100 * $lineItemTax['totalTaxAmount'] / $lineItemTax['taxableAmount'], 4);
        }

        $extensionAttributes->setTaxAmount($lineItemTax['totalTaxAmount']);
        $extensionAttributes->setTaxPercent($taxPercent);
        $extensionAttributes->setProductType($item->getProductType());
        $extensionAttributes->setPriceType($item->getProduct()->getPriceType());
        $extensionAttributes->setData('sku', $item->getProduct()->getSku());
        $extensionAttributes->setData('product_name', $item->getProduct()->getName());
        $extensionAttributes->setData('product_id', $item->getProduct()->getId());

        $jurisdictionRates = [];
        if ($lineItemTax !== null && array_key_exists('jurisdiction', $lineItemTax)) {
            $jurisdictions = (
                is_array($lineItemTax['jurisdiction'])
            ) ? $lineItemTax['jurisdiction'] : [$lineItemTax['jurisdiction']];

            foreach ($jurisdictions as $jurisdiction) {
                if ($jurisdiction->rate <= 0 || $jurisdiction->taxAmount <= 0) {
                    continue;
                }

                if (empty($jurisdictionRates[$jurisdiction->name . ' ' . $jurisdiction->taxName])) {
                    $jurisdictionRates[$jurisdiction->name . ' ' . $jurisdiction->taxName] = [
                        'rate' => 100 * $jurisdiction->rate,
                        'amount' => $jurisdiction->taxAmount
                    ];
                }
            }
        }

        $extensionAttributes->setJurisdictionTaxRates($jurisdictionRates);

        $itemDataObject->setExtensionAttributes($extensionAttributes);

        return $itemDataObject;
    }

    private function isTaxApplicable(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment)
    {
        $isEnabled = $this->taxConfig->isTaxEnabled();
        $isCountryApplicable = in_array(
            $shippingAssignment->getShipping()->getAddress()->getCountry(),
            explode(',', $this->taxConfig->getTaxCountries())
        );

        return ($isEnabled && $isCountryApplicable);
    }
}
