<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\ValueObject\EventType;

class ViewEventMapper extends EventMapper
{
    public static function table(): string
    {
        return 'view_events';
    }

    protected static function getEventType(): string
    {
        return EventType::VIEW;
    }
}
