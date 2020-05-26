<?php

namespace Payments\SecureAcceptance\Model\Ui;

/**
 * Class SecureTokenConfigProvider
 */
class SecureTokenConfigProvider
{
    /**
     * @var \Payments\SecureAcceptance\Model\SecureToken\Generator
     */
    private $generator;

    /**
     * SecureTokenConfigProvider constructor.
     *
     * @param \Payments\SecureAcceptance\Model\SecureToken\Generator $generator
     */
    public function __construct(\Payments\SecureAcceptance\Model\SecureToken\Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE => [
                    'secure_token' => $this->generator->get(),
                ],
            ]
        ];
    }
}
