<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Mapper;

class ClickEventMapper extends EventMapper
{
    public static function table(): string
    {
        return 'click_events';
    }
}
