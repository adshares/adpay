<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;
use App\Domain\ValueObject\PaymentReportStatus;
use DateTimeInterface;

interface PaymentReportRepository
{
    public function fetch(int $id): ?PaymentReport;

    public function fetchOrCreate(int $id): PaymentReport;

    public function fetchAll(): PaymentReportCollection;

    public function fetchById(int ...$ids): PaymentReportCollection;

    public function fetchByStatus(PaymentReportStatus ...$statuses): PaymentReportCollection;

    public function save(PaymentReport $report): void;

    public function deleteByTime(?DateTimeInterface $timeStart = null, ?DateTimeInterface $timeEnd = null): int;
}
