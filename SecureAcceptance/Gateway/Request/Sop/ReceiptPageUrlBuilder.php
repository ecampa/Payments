<?php
namespace Payments\SecureAcceptance\Gateway\Request\Sop;


class ReceiptPageUrlBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        return [
            'override_custom_receipt_page' => $this->urlBuilder->getUrl(
                'paymentssa/index/placeorder',
                ['_secure' => true]
            ),
            'override_custom_cancel_page' => $this->urlBuilder->getUrl(
                'paymentssa/index/cancel',
                ['_secure' => true]
            ),
        ];
    }
}
