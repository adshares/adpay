<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Repository;

use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;

final class DoctrinePaymentReportRepository extends DoctrineModelUpdater implements PaymentReportRepository
{
    public function fetch(int $id): PaymentReport
    {
        return new PaymentReport($id, PaymentReportStatus::createIncomplete());
    }

    public function update(PaymentReport $report): void
    {
    }
}
