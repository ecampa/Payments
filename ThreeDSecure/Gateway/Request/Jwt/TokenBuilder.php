<?php
namespace Payments\ThreeDSecure\Gateway\Request\Jwt;

class TokenBuilder implements \Payments\ThreeDSecure\Gateway\Request\Jwt\TokenBuilderInterface
{

    const JWT_LIFETIME = 3600;

    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256
     */
    private $sha256;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var \Lcobucci\JWT\Signer\KeyFactory
     */
    private $keyFactory;

    /**
     * @var \Lcobucci\JWT\BuilderFactory
     */
    private $builderFactory;

    public function __construct(
        \Lcobucci\JWT\Signer\Hmac\Sha256 $sha256,
        \Magento\Framework\Math\Random $random,
        \Lcobucci\JWT\Signer\KeyFactory $keyFactory,
        \Lcobucci\JWT\BuilderFactory $builderFactory
    ) {

        $this->sha256 = $sha256;
        $this->random = $random;
        $this->keyFactory = $keyFactory;
        $this->builderFactory = $builderFactory;
    }

    public function buildToken($referenceId, $payload, $orgUnitId, $apiId, $apiKey)
    {
        /** @var \Lcobucci\JWT\Builder $tokenBuilder */
        $tokenBuilder = $this->builderFactory->create();

        $jwtId = $this->random->getUniqueHash('jwt_');
        $currentTime = $this->getTime();

        $jwt = $tokenBuilder
            ->identifiedBy($jwtId, true)
            ->issuedBy($apiId)
            ->issuedAt($currentTime)
            ->expiresAt($currentTime + self::JWT_LIFETIME)
            ->withClaim('OrgUnitId', $orgUnitId)
            ->withClaim('ReferenceId', $referenceId)
            ->withClaim('Payload', $payload)
            ->withClaim('ObjectifyPayload', true)
            ->getToken($this->sha256, $this->keyFactory->create(['content' => $apiKey]));

        return $jwt->__toString();
    }

    protected function getTime()
    {
        return time();     }
}
