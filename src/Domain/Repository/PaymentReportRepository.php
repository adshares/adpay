<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Model\PaymentReportCollection;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use DateTimeInterface;

interface PaymentReportRepository
{
    public function fetch(int $id): PaymentReport;

    public function fetchByStatus(PaymentReportStatus ...$statuses): PaymentReportCollection;

    public function save(PaymentReport $report): void;

    public function deleteByTime(?DateTimeInterface $timeStart = null, ?DateTimeInterface $timeEnd = null): int;
}
