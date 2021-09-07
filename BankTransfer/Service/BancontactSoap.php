<?php
namespace Payments\BankTransfer\Service;

use Payments\BankTransfer\Model\Config;
use Payments\BankTransfer\Helper\RequestDataBuilder;
use Payments\Core\Model\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;


class BancontactSoap extends \Payments\BankTransfer\Service\SoapAPI{


    /**
     * SoapAPI for Bancontact
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param RequestDataBuilder $dataBuilder
     * @param Config $gatewayConfig
     * @param \SoapClient|null $client
     * @throws \Exception
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\BankTransfer\Helper\RequestDataBuilder $dataBuilder,
        \Payments\BankTransfer\Model\Config $gatewayConfig,
        \SoapClient $client = null
    ) {
        parent::__construct(
            $scopeConfig,
            $logger,
            $dataBuilder,
            $gatewayConfig,
            'bancontact',
            $client
        );
    }
}