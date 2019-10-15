<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\PaymentCollection;
use DateTimeInterface;

interface PaymentRepository
{
    public function deleteTimeInterval(
        ?DateTimeInterface $timeStart,
        ?DateTimeInterface $timeEnd
    ): void;

    public function saveAll(PaymentCollection $events): int;

    public function deleteByReportId(int $reportId): void;

    public function fetchByReportId(int $reportId, ?int $limit = null, ?int $offset = null): iterable;
}
