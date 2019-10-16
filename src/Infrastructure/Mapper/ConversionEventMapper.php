<?php declare(strict_types = 1);

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
                'conversion_id' => $event->getConversionId()->toBin(),
                'conversion_value' => $event->getConversionValue(),
            ]
        );
    }

    public static function types(): array
    {
        return array_merge(
            parent::types(),
            [
                'conversion_id' => Type::BINARY,
                'conversion_value' => Type::INTEGER,
            ]
        );
    }

    protected static function getEventType(): string
    {
        return EventType::CONVERSION;
    }
}
