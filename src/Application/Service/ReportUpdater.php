<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Service;

use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;

interface ReportUpdater
{
    /**
     * @param EventType $type
     * @param DateTimeInterface $timeStart
     * @param DateTimeInterface $timeEnd
     */
    public function noticeEvents(
        EventType $type,
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd
    ): void;
}
