<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Model\PaymentReport;

interface PaymentReportRepository
{
    public function fetch(int $id): PaymentReport;

    public function update(PaymentReport $report): void;
}
