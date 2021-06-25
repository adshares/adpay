<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Doctrine\DBAL\Types\Type;

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
                'group_id' => Type::BINARY,
                'conversion_id' => Type::BINARY,
                'conversion_value' => Type::INTEGER,
                'payment_status' => Type::INTEGER,
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
