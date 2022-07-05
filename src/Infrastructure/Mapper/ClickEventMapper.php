<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\ValueObject\EventType;

class ClickEventMapper extends EventMapper
{
    public static function table(): string
    {
        return 'click_events';
    }

    protected static function getEventType(): string
    {
        return EventType::CLICK;
    }
}
