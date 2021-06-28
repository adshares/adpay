<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Doctrine\DBAL\Types\Types;

class ConversionEventMapper extends EventMapper
{
    public static function table(): string
    {
        return 'conversion_events';
    }

    public static function map(Event $event): array
    {
        /* @var $event ConversionEvent */
        return array_merge(
            parent::map($event),
            [
                'group_id' => $event->getGroupId()->toBin(),
                'conversion_id' => $event->getConversionId()->toBin(),
                'conversion_value' => $event->getConversionValue(),
                'payment_status' => $event->getPaymentStatus()->getStatus(),
            ]
        );
    }

    public static function types(): array
    {
        return array_merge(
            parent::types(),
            [
                'group_id' => Types::BINARY,
                'conversion_id' => Types::BINARY,
                'conversion_value' => Types::INTEGER,
                'payment_status' => Types::INTEGER,
            ]
        );
    }

    protected static function getEventType(): string
    {
        return EventType::CONVERSION;
    }

    public static function fillRaw(array $row): array
    {
        return array_merge(
            parent::fillRaw($row),
            [
                'group_id' => bin2hex($row['group_id']),
                'conversion_id' => bin2hex($row['conversion_id']),
                'conversion_value' => (int)$row['conversion_value'],
                'payment_status' => $row['payment_status'] !== null ? (int)$row['payment_status'] : null,
            ]
        );
    }
}
