<?php
namespace Payments\ThreeDSecure\Gateway\Request\Cca;

class BuilderStrategy implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\ThreeDSecure\Gateway\Request\Cca\PayerAuthEnrollBuilder
     */
    private $enrollBuilder;

    /**
     * @var \Payments\ThreeDSecure\Gateway\Request\Cca\PayerAuthValidateBuilder
     */
    private $validateBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\ThreeDSecure\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\SecureAcceptance\Helper\RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var bool
     */
    private $isAdminhtml;

    public function __construct(
        \Payments\ThreeDSecure\Gateway\Request\Cca\PayerAuthEnrollBuilder $enrollBuilder,
        \Payments\ThreeDSecure\Gateway\Request\Cca\PayerAuthValidateBuilder $validateBuilder,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\ThreeDSecure\Gateway\Config\Config $config,
        $isAdminhtml = false
    ) {
        $this->enrollBuilder = $enrollBuilder;
        $this->validateBuilder = $validateBuilder;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->isAdminhtml = $isAdminhtml;
    }

    /**
     * Strategy method to determine whether paEnroll or paValidate or empty request should be built
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!$this->config->isEnabled() || $this->isAdminhtml) {
            return [];
        }

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        /** @var \Magento\Quote\Api\Data\PaymentInterface $payment */
        $extensionAttributes = $payment->getExtensionAttributes();

        $cardType = $this->requestDataBuilder->getCardType($payment->getAdditionalInformation('cardType'), true);

        if (!in_array($cardType, $this->config->getEnabledCards())) {
            return [];
        }

        if ($extensionAttributes && $extensionAttributes->getCcaResponse()) {
            return $this->validateBuilder->build($buildSubject);
        }

        return $this->enrollBuilder->build($buildSubject);
    }
}
