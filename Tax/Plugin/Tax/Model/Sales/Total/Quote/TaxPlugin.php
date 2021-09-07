<?php
namespace Payments\Tax\Plugin\Tax\Model\Sales\Total\Quote;

class TaxPlugin
{
    /**
     * @var \Payments\Tax\Model\FinalTaxCollectionSemaphore
     */
    private $finalTaxCollectionSemaphore;

    public function __construct(
        \Payments\Tax\Model\FinalTaxCollectionSemaphore $finalTaxCollectionSemaphore
    ) {
        $this->finalTaxCollectionSemaphore = $finalTaxCollectionSemaphore;
    }

    public function beforeCollect()
    {
        $this->finalTaxCollectionSemaphore->setIsFinalTax(true);
    }

    public function afterCollect()
    {
        $this->finalTaxCollectionSemaphore->setIsFinalTax(false);
    }

}
