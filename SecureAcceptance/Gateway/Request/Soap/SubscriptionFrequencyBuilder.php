<?php
namespace Payments\SecureAcceptance\Gateway\Request\Soap;


class SubscriptionFrequencyBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {
        return [
            'recurringSubscriptionInfo' => [
                'frequency' => 'on-demand',
            ],
            'subsequentAuthFirst' => 'true'
        ];
    }
}
