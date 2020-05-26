<?php
namespace Payments\ECheck\Gateway\Response;


class NotificationOfChangesMapper implements \Payments\Core\Gateway\Response\MapperInterface
{

    const KEY_CONVERSION_DETAILS  = 'conversionDetails';
    const KEY_MRN = 'merchantReferenceNumber';
    const KEY_NEW_DECISION = 'newDecision';
    const KEY_DECISION = 'originalDecision';
    const KEY_REQUEST_ID = 'requestId';

    /**
     * @inheritDoc
     */
    public function map(array $handlingSubject, array $response)
    {

        $result = [];

        return $result;
    }

}
