<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Doctrine\DBAL\Types\Type;

class PaymentMapper
{
    public static function table(): string
    {
        return 'payments';
    }

    public static function map(array $payment): array
    {
        return [
            'report_id' => $payment['report_id'],
            'event_id' => hex2bin($payment['event_id']),
            'event_type' => $payment['event_type'],
            'status' => $payment['status'],
            'value' => $payment['value'],
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::INTEGER,
            'report_id' => Type::INTEGER,
            'event_id' => Type::BINARY,
            'event_type' => Type::STRING,
            'status' => Type::INTEGER,
            'value' => Type::INTEGER,
        ];
    }

    public static function fillRaw(array $row): array
    {
        return [
            'id' => (int)$row['id'],
            'report_id' => (int)$row['report_id'],
            'event_id' => bin2hex($row['event_id']),
            'event_type' => $row['event_type'],
            'status' => (int)$row['status'],
            'value' => $row['value'] === null ? null : (int)$row['value'],
        ];
    }
}
