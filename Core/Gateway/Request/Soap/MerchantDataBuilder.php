<?php
namespace Payments\Core\Gateway\Request\Soap;

class MerchantDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\Core\Model\AbstractGatewayConfig
     */
    private $config;

    /**
     * @param \Payments\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param \Payments\Core\Model\AbstractGatewayConfig $config
     */
    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\Core\Model\AbstractGatewayConfig $config
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $request = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $storeId = $paymentDO->getOrder()->getStoreId();

        $request['partnerSolutionID'] = \Payments\Core\Helper\AbstractDataBuilder::PARTNER_SOLUTION_ID;
        $request['merchantID'] = $this->config->getMerchantId($storeId);
        $request['storeId'] = $storeId;

        if ($developerId = $this->config->getDeveloperId()) {
            $request['developerId'] = $developerId;
        }

        return $request;
    }
}
