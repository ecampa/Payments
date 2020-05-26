<?php

namespace Payments\Core\Plugin;

use Payments\Core\Model\Logger;
use Payments\Core\Model\LoggerInterface;

class LoggerCensorPlugin
{
    /**
     * @var Logger\Censor
     */
    private $censor;

    /**
     * @param Logger\Censor $censor
     */
    public function __construct(\Payments\Core\Model\Logger\Censor $censor)
    {
        $this->censor = $censor;
    }

    /**
     * @param LoggerInterface $subject
     * @param $data
     * @param array $context
     * @return array
     */
    public function beforeDebug(\Payments\Core\Model\LoggerInterface $subject, $data, array $context = [])
    {
        return [$this->censor->censor($data), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeEmergency(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeAlert(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeCritical(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeError(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeWarning(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeNotice(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeInfo(\Payments\Core\Model\LoggerInterface $subject, $message, array $context = [])
    {
        return [$this->censor->censor($message), $context];
    }

    /**
     * @param LoggerInterface $subject
     * @param $level
     * @param $message
     * @param array $context
     * @return array
     */
    public function beforeLog(\Payments\Core\Model\LoggerInterface $subject, $level, $message, array $context = [])
    {
        return [$level, $this->censor->censor($message), $context];
    }
}
