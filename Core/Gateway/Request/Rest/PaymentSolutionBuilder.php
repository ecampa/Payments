<?php
namespace Payments\Core\Gateway\Request\Rest;


class PaymentSolutionBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var string|null
     */
    private $paymentSolutionId;

    /**
     * PaymentSolutionBuilder constructor.
     *
     * @param string|null $paymentSolutionId
     */
    public function __construct(
        $paymentSolutionId = null
    ) {
        $this->paymentSolutionId = $paymentSolutionId;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        if (!$this->paymentSolutionId) {
            return [];
        }

        return [
            'processingInformation' => [
                'paymentSolution' => $this->paymentSolutionId,
            ]
        ];
    }
}
