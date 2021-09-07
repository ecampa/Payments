<?php
namespace Payments\Core\Service;


interface OrderToQuoteInterface
{

    /**
     * @param $orderId
     * @param null $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function convertOrderToQuote($orderId, $quote = null);

}
