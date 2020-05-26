<?php
namespace Payments\Core\Gateway\Request\Rest;


class TimeIntervalBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{

    const REPORT_INTERVAL = 24 * 3600;
    const DATE_FORMAT = 'c';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function build(array $buildSubject)
    {

        $gmtTimestamp = $this->dateTime->gmtTimestamp();
        $interval = $buildSubject['interval'] ?? static::REPORT_INTERVAL;

        $startDateTime = $buildSubject['startTime']
            ?? $this->dateTime->gmtDate(static::DATE_FORMAT, $gmtTimestamp - $interval);

        $endDateTime = $buildSubject['endTime']
            ?? $this->dateTime->gmtDate(static::DATE_FORMAT, $gmtTimestamp);

        return [
            'startTime' => $startDateTime,
            'endTime' => $endDateTime,
        ];
    }
}
