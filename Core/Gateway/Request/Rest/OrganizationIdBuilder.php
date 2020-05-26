<?php
namespace Payments\Core\Gateway\Request\Rest;


class OrganizationIdBuilder  implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\Core\Model\Config
     */
    private $config;

    public function __construct(
        \Payments\Core\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        $storeId = $buildSubject['store_id'] ?? null;

        return [
            'organizationId' => $this->config->getMerchantId($storeId),
        ];
    }
}
