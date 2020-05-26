<?php
namespace Payments\Core\Gateway\Request\Rest;


class SfsFileIdBuilder  implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        $fileId = $buildSubject['fileId'] ?? null;

        if (!$fileId) {
            throw new \InvalidArgumentException('File Id must be provided.');
        }

        return [\Payments\Core\Gateway\Http\Client\Rest::KEY_URL_PARAMS => [$fileId]];
    }
}
