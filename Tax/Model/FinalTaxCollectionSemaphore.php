<?php
namespace Payments\Tax\Model;


class FinalTaxCollectionSemaphore
{

    /**
     * @var bool
     */
    private $isFinalTax = false;

    /**
     * @return bool
     */
    public function isFinalTax(): bool
    {
        return $this->isFinalTax;
    }

    /**
     * @param bool $isFinalTax
     */
    public function setIsFinalTax(bool $isFinalTax)
    {
        $this->isFinalTax = $isFinalTax;
    }


}
