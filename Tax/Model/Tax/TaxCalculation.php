<?php

namespace Payments\Tax\Model\Tax;

use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use Magento\Store\Model\StoreManagerInterface;

class TaxCalculation extends \Magento\Tax\Model\TaxCalculation
{
    /**
     * @var \Magento\Tax\Api\Data\AppliedTaxInterfaceFactory
     */
    protected $appliedTaxFactory;

    /**
     * @var \Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory
     */
    protected $appliedTaxRateFactory;

    /**
     * @var QuoteDetailsItemInterface[]
     */
    protected $quoteDetailItemsToProcess;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param Calculation $calculation
     * @param CalculatorFactory $calculatorFactory
     * @param Config $config
     * @param TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param TaxClassManagementInterface $taxClassManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory
     * @param AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
     */
    public function __construct(
        Calculation $calculation,
        CalculatorFactory $calculatorFactory,
        Config $config,
        TaxDetailsInterfaceFactory $taxDetailsDataObjectFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemDataObjectFactory,
        StoreManagerInterface $storeManager,
        TaxClassManagementInterface $taxClassManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        AppliedTaxInterfaceFactory $appliedTaxDataObjectFactory,
        AppliedTaxRateInterfaceFactory $appliedTaxRateDataObjectFactory
    ) {
        $this->appliedTaxFactory = $appliedTaxDataObjectFactory;
        $this->appliedTaxRateFactory = $appliedTaxRateDataObjectFactory;
        $this->priceCurrency = $priceCurrency;

        return parent::__construct(
            $calculation,
            $calculatorFactory,
            $config,
            $taxDetailsDataObjectFactory,
            $taxDetailsItemDataObjectFactory,
            $storeManager,
            $taxClassManagement,
            $dataObjectHelper
        );
    }

    /**
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails
     * @param bool $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface $scope
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    public function customCalculateTax(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails,
        $useBaseCurrency,
        $scope
    ) {
        $initTaxDetailsData = $this->getEmptyTaxDetails();

        $items = $quoteDetails->getItems();

        if (empty($items)) {
            return $this->taxDetailsDataObjectFactory->create($this->getEmptyTaxDetails());
        }

        $itemsReady = $this->processItems($items, $useBaseCurrency, $scope, $initTaxDetailsData);

        $taxDetailsDataObject = $this->taxDetailsDataObjectFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $taxDetailsDataObject,
            $initTaxDetailsData,
            \Magento\Tax\Api\Data\TaxDetailsInterface::class
        );
        $taxDetailsDataObject->setItems($itemsReady);
        return $taxDetailsDataObject;
    }

    protected function processItems($items, $useBaseCurrency, $scope, $initTaxDetailsData)
    {
        $itemsToProcess = [];
        $parentToChildren = [];

        foreach ($items as $item) {
            if ($item->getParentCode() === null) {
                $itemsToProcess[$item->getCode()] = $item;
            } else {
                $parentToChildren[$item->getParentCode()][] = $item;
            }
        }

        $this->quoteDetailItemsToProcess = $itemsToProcess;

        $itemsReady = [];
        /** @var QuoteDetailsItemInterface $item */
        foreach ($itemsToProcess as $item) {
            if (isset($parentToChildren[$item->getCode()])) {
                $processedChildren = [];
                foreach ($parentToChildren[$item->getCode()] as $child) {
                    $processedItem = $this->processItemWithoutCalculator($child, $useBaseCurrency, $scope);
                    $initTaxDetailsData = $this->aggregateItemData($initTaxDetailsData, $processedItem);
                    $itemsReady[$processedItem->getCode()] = $processedItem;
                    $processedChildren[] = $processedItem;
                }
                $processedItem = $this->calculateParent($processedChildren, $item->getQuantity());
                $processedItem->setCode($item->getCode());
                $processedItem->setType($item->getType());
            } else {
                $processedItem = $this->processItemWithoutCalculator($item, $useBaseCurrency, $scope);
                $initTaxDetailsData = $this->aggregateItemData($initTaxDetailsData, $processedItem);
            }
            $itemsReady[$processedItem->getCode()] = $processedItem;
        }

        return $itemsReady;
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param bool $useBaseCurrency
     * @param \Magento\Framework\App\ScopeInterface $scope
     * @return \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected function processItemWithoutCalculator(
        QuoteDetailsItemInterface $item,
        $useBaseCurrency,
        $scope
    ) {
        $quoteItemUnitPrice = $item->getUnitPrice();
        $quantity = $this->getTotalQuantity($item);

        $extensionAttributes = $item->getExtensionAttributes();
        $taxAmount = $extensionAttributes ? $extensionAttributes->getTaxAmount() : 0;
        $taxPercent = $extensionAttributes ? $extensionAttributes->getTaxPercent() : 0;

        if (!$useBaseCurrency) {
            $taxAmount = $this->priceCurrency->convert($taxAmount, $scope);
        }

        $rowTotal = $quoteItemUnitPrice * $quantity;
        $rowTotalInclTax = $rowTotal + $taxAmount;

        $priceInclTax = $rowTotalInclTax / $quantity;

        $appliedTax = $this->getAppliedTax($item, $scope);

        return $this->taxDetailsItemDataObjectFactory->create()
             ->setCode($item->getCode())
             ->setType($item->getType())
             ->setRowTax($taxAmount)
             ->setPrice($quoteItemUnitPrice)
             ->setPriceInclTax($priceInclTax)
             ->setRowTotal($rowTotal)
             ->setRowTotalInclTax($rowTotalInclTax)
             ->setDiscountTaxCompensationAmount(0)
             ->setAssociatedItemCode($item->getAssociatedItemCode())
             ->setTaxPercent($taxPercent)
             ->setAppliedTaxes([$appliedTax->getTaxRateKey() => $appliedTax]);
    }

    /**
     * @param QuoteDetailsItemInterface $item
     * @param $scope
     * @return \Magento\Tax\Api\Data\AppliedTaxInterface
     */
    protected function getAppliedTax(
        QuoteDetailsItemInterface $item,
        $scope
    ) {
        $extensionAttributes = $item->getExtensionAttributes();
        if (!$extensionAttributes) {
            return $this->appliedTaxFactory->create()
                ->setAmount(0)
                ->setPercent(0)
                ->setTaxRateKey('')
                ->setRates([])
                ;
        }
        $taxAmount = $extensionAttributes->getTaxAmount() ?? 0;
        $taxAmount = $this->priceCurrency->convert($taxAmount, $scope);
        $taxPercent = $extensionAttributes->getTaxPercent() ?? 0;
        $jurisdictionTaxRates = $extensionAttributes->getJurisdictionTaxRates() ?? [];
        $rateDataObjects = [];

        foreach ($jurisdictionTaxRates as $jurisdiction => $jurisdictionTaxRate) {
            $jurisdictionTitle = ucfirst($jurisdiction) . ' Tax';

            $rateDataObjects[$jurisdiction] = $this->appliedTaxRateFactory->create()
                ->setPercent($jurisdictionTaxRate['rate'])
                ->setCode($jurisdiction)
                ->setTitle($jurisdictionTitle);
        }

        $appliedTaxDataObject = $this->appliedTaxFactory->create();
        $appliedTaxDataObject->setAmount($taxAmount);
        $appliedTaxDataObject->setPercent($taxPercent);
        $appliedTaxDataObject->setTaxRateKey(implode(' - ', array_keys($jurisdictionTaxRates)));
        $appliedTaxDataObject->setRates($rateDataObjects);

        return $appliedTaxDataObject;
    }

    protected function getTotalQuantity(QuoteDetailsItemInterface $item)
    {
        if ($item->getParentCode()) {
            $parentQuantity = $this->quoteDetailItemsToProcess[$item->getParentCode()]->getQuantity();
            return $parentQuantity * $item->getQuantity();
        }
        return $item->getQuantity();
    }

    private function getEmptyTaxDetails()
    {
        return [
            TaxDetails::KEY_SUBTOTAL => 0.0,
            TaxDetails::KEY_TAX_AMOUNT => 0.0,
            TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            TaxDetails::KEY_APPLIED_TAXES => [],
            TaxDetails::KEY_ITEMS => [],
        ];
    }
}
