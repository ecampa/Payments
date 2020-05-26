<?php

namespace Payments\SecureAcceptance\Gateway\Config;

class PlaceOrderUrlHandler implements \Magento\Payment\Gateway\Config\ValueHandlerInterface
{

    /**
     * @var bool
     */
    private $isAdmin;

    public function __construct(
        bool $isAdmin = false
    ) {
        $this->isAdmin = $isAdmin;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {

        if ($this->isAdmin) {
            return 'paymentssaadmin/transparent/requestSilentData';
        }

        return 'paymentssa/index/loadSilentData';
    }
}
