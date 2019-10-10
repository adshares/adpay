<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Mapper;

class ViewEventMapper extends EventMapper
{
    public static function table(): string
    {
        return 'view_events';
    }
}
