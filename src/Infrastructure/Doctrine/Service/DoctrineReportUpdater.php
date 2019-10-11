<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Service\ReportUpdater;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;

final class DoctrineReportUpdater extends DoctrineModelUpdater implements ReportUpdater
{
    public function noticeEvents(
        EventType $type,
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd
    ): void {
    }
}
