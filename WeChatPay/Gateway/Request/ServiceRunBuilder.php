<?php
namespace Payments\WeChatPay\Gateway\Request;

class ServiceRunBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface[]
     */
    private $builders;

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(
        \Magento\Framework\ObjectManager\TMapFactory $tmapFactory,
        string $serviceName,
        array $builders = []
    ) {
        $this->serviceName = $serviceName;
        $this->builders = $tmapFactory->create([
            'array' => $builders,
            'type' => \Magento\Payment\Gateway\Request\BuilderInterface::class
        ]);
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $result = [];
        foreach ($this->builders as $builder) {
            $result = array_merge($result, $builder->build($buildSubject));
        }

        $result['run'] = 'true';
        return [$this->serviceName => $result];
    }
}
