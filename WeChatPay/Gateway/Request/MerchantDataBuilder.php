<?php
namespace Payments\WeChatPay\Gateway\Request;

class MerchantDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\WeChatPay\Gateway\Config\Config
     */
    private $config;

    /**
     * @param \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     * @param \Payments\WeChatPay\Gateway\Config\Config $config
     */
    public function __construct(
        \Payments\WeChatPay\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\WeChatPay\Gateway\Config\Config $config
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
        $request['storeId'] = $storeId;

        if ($developerId = $this->config->getDeveloperId()) {
            $request['developerId'] = $developerId;
        }

        return $request;
    }
}
