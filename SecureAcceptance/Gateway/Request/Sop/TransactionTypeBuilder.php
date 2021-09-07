<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class TransactionTypeBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Payments\SecureAcceptance\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    public function __construct(\Payments\SecureAcceptance\Gateway\Helper\SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    public function build(array $buildSubject)
    {

        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();

        $operations = ['authorization'];

        if (
            $payment->getMethodInstance()->getConfigPaymentAction()
            == \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE
        ) {
            $operations = ['sale'];
        }

        if ($payment->getAdditionalInformation(\Magento\Vault\Model\Ui\VaultConfigProvider::IS_ACTIVE_CODE)) {
            $operations[] = 'create_payment_token';
        }

        return ['transaction_type' => implode(',', $operations)];
    }
}
