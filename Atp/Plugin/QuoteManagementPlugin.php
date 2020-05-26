<?php

namespace Payments\Atp\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;

class QuoteManagementPlugin extends \Payments\Atp\Plugin\AbstractAtpPlugin
{
    /**
     * @param QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
        $orderData = []
    ) {
        $this->preventFurtherAtpProcessing();

        return [$quote, $orderData];
    }
}
