<?php
namespace Payments\Tax\Plugin\Tax\Api;


class TaxCalculationInterfacePlugin
{

    /**
     * @var \Payments\Tax\Model\Config
     */
    private $config;

    /**
     * @var \Payments\Tax\Model\Calculator
     */
    private $calculator;

    public function __construct(
        \Payments\Tax\Model\Config $config,
        \Payments\Tax\Model\Calculator $calculator
    ) {
        $this->config = $config;
        $this->calculator = $calculator;
    }

    /**
     * @param \Magento\Tax\Api\TaxCalculationInterface $subject
     * @param callable $proceed
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails
     * @param null $storeId
     * @param bool $round
     *
     * @return \Magento\Tax\Api\Data\TaxDetailsInterface
     */
    public function aroundCalculateTax(
        \Magento\Tax\Api\TaxCalculationInterface $subject,
        callable $proceed,
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteDetails,
        $storeId = null,
        $round = true
    ) {
        if (!$this->config->isTaxEnabled($storeId)) {
            return $proceed($quoteDetails, $storeId, $round);
        }

        return $this->calculator->calculate($quoteDetails, $storeId, $round);
    }

}
