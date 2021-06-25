<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\ValueObject\EventType;

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
