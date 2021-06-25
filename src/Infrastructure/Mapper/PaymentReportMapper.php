<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Doctrine\DBAL\Types\Type;

class PaymentReportMapper
{
    public static function table(): string
    {
        return 'payment_reports';
    }

    public static function map(PaymentReport $report): array
    {
        return [
            'id' => $report->getId(),
            'status' => $report->getStatus()->getStatus(),
            'intervals' => $report->getIntervals(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::INTEGER,
            'status' => Type::INTEGER,
            'intervals' => Type::JSON,
        ];
    }

    public static function fill(array $row): PaymentReport
    {
        return new PaymentReport(
            (int)$row['id'],
            new PaymentReportStatus((int)$row['status']),
            json_decode($row['intervals'], true)
        );
    }
}
