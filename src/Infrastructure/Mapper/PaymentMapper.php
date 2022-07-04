<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use Doctrine\DBAL\Types\Types;

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
            'id' => Types::INTEGER,
            'report_id' => Types::INTEGER,
            'event_id' => Types::BINARY,
            'event_type' => Types::STRING,
            'status' => Types::INTEGER,
            'value' => Types::INTEGER,
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
