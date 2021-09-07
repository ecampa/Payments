<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class SignatureDecorator implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Magento\Framework\ObjectManager\TMap
     */
    private $builders;

    /**
     * @var \Payments\SecureAcceptance\Model\SignatureManagementInterface
     */
    private $signatureManagement;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface
     */
    private $configProvider;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Magento\Framework\ObjectManager\TMapFactory $tmapFactory,
        \Payments\SecureAcceptance\Model\SignatureManagementInterface $signatureManagement,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Payments\SecureAcceptance\Gateway\Config\SaConfigProviderInterface $configProvider,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        array $builders = []
    ) {
        $this->signatureManagement = $signatureManagement;
        $this->dateTime = $dateTime;
        $this->configProvider = $configProvider;
        $this->subjectReader = $subjectReader;

        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => \Magento\Payment\Gateway\Request\BuilderInterface::class,
            ]
        );
    }

    public function build(array $buildSubject)
    {
        $result = [];

        foreach ($this->builders as $builder) {
            $result = $this->merge($result, $builder->build($buildSubject));
        }

        $result['signed_date_time'] = $this->dateTime->gmtDate('Y-m-d\\TH:i:s\\Z');
        $result = $this->filterParams($result);

        // important to have signed_field_names present in the signed fields list, or the 403 error is returned.
        $result['signed_field_names'] = '';
        $result['signed_field_names'] = implode(',', array_keys($result));

        $order = $this->subjectReader->readPayment($buildSubject)->getOrder();
        $secretKey = $this->configProvider->getSecretKey($order->getStoreId());

        $result['signature'] = $this->signatureManagement->sign($result, $secretKey);

        return $result;
    }

    private function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }

    private function filterParams($params)
    {
        return array_map(
            function ($value) {
                return (string)$value;
            },
            array_filter(
                $params,
                function ($value) {
                    return !is_null($value) && $value !== '';
                }
            )
        );
    }
}
