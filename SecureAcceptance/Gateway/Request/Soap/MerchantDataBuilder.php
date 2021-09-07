<?php


namespace Payments\SecureAcceptance\Gateway\Request\Soap;

use Payments\SecureAcceptance\Gateway\Config\Config;

class MerchantDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $gatewayConfig;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Gateway\Config\Config $gatewayConfig
    ) {
        $this->subjectReader = $subjectReader;
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * Builds Merchant Data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $request['partnerSolutionID'] = \Payments\Core\Helper\AbstractDataBuilder::PARTNER_SOLUTION_ID;
        $request['storeId'] = $paymentDO->getOrder()->getStoreId();
        $request['merchantID'] = $this->gatewayConfig->getValue(
            \Payments\SecureAcceptance\Gateway\Config\Config::KEY_MERCHANT_ID,
            $paymentDO->getOrder()->getStoreId()
        );
        $developerId = $this->gatewayConfig->getDeveloperId();
        if (!empty($developerId)) {
            $request['developerId'] = $developerId;
        }

        return $request;
    }
}
