<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\Payment;

interface PaymentRepository
{
    public function fetchByReportId(int $reportId, ?int $limit = null, ?int $offset = null): iterable;

    public function save(Payment $payment): void;

    public function deleteByReportId(int $reportId): void;
}
