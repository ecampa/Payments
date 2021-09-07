<?php
namespace Payments\Tax\Service;


interface TaxServiceInterface
{
    /**
     *
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getTaxForOrder(
        \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails,
        $storeId = null
    );
}
