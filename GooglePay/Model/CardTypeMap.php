<?php
namespace Payments\GooglePay\Model;


class CardTypeMap
{

    const TYPE_MAP = [
        'AE' => 'AMEX',
        'DI' => 'DISCOVER',
        'JCB' => 'JCB',
        'MC' => 'MASTERCARD',
        'VI' => 'VISA',
    ];

    public function toMagentoType($cardType)
    {
        $magentoType = array_search($cardType, static::TYPE_MAP);

        if ($magentoType === false) {
            throw new \InvalidArgumentException('No matching card type found');
        }

        return $magentoType;
    }

    public function toGooglePayType($cardType)
    {
        if (!isset(static::TYPE_MAP[$cardType])) {
            throw new \InvalidArgumentException('No matching card type found');
        }

        return static::TYPE_MAP[$cardType];
    }

}
