<?php
namespace Payments\Core\Gateway\Request\Rest;

class PartnerInformationBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Payments\Core\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @var \Payments\Core\Model\Config
     */
    private $config;

    public function __construct(
        \Payments\Core\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\Core\Model\Config $config)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        return [
            'clientReferenceInformation' => [
                'partner' => [
                    'developerId' => $this->config->getDeveloperId(),
                    'solutionId' => \Payments\Core\Helper\AbstractDataBuilder::PARTNER_SOLUTION_ID,
                ]
            ]
        ];
    }
}
