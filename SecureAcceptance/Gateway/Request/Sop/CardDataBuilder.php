<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;

use Payments\SecureAcceptance\Helper\RequestDataBuilder;

class CardDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder
    ) {
        $this->config = $config;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        $request = [
            'payment_method' => 'card',
        ];

        if (!$this->config->isSilent()) {
            //hosted checkout, no need to pass unsigned fields
            return $request;
        }

        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();

        if ($ccType = $payment->getAdditionalInformation('cardType')) {
            $request['card_type'] = $this->requestDataBuilder->getCardType($ccType);
            $request['card_type_selection_indicator'] = \Payments\SecureAcceptance\Helper\RequestDataBuilder::CARD_TYPE_SELECTION_INDICATOR_BY_CARDHOLDER;
        }

        $unsignedFields = ['card_number', 'card_expiry_date'];

        if (!$this->config->getIgnoreCvn()) {
            $unsignedFields[] = 'card_cvn';
        }

        $request['unsigned_field_names'] = implode(',', $unsignedFields);

        return $request;
    }
}
