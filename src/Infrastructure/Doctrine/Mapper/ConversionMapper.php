<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Mapper;

use Adshares\AdPay\Domain\Model\Conversion;
use Doctrine\DBAL\Types\Type;

class ConversionMapper
{
    public static function map(Conversion $conversion): array
    {
        return [
            'id' => $conversion->getId()->toBin(),
            'campaign_id' => $conversion->getCampaignId()->toBin(),
            '`limit`' => $conversion->getLimitValue(),
            'limit_type' => $conversion->getLimitType()->toString(),
            'cost' => $conversion->getCost(),
            'value' => $conversion->getValue(),
            'is_value_mutable' => $conversion->isValueMutable(),
            'is_repeatable' => $conversion->isRepeatable(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            '`limit`' => Type::INTEGER,
            'limit_type' => Type::STRING,
            'cost' => Type::INTEGER,
            'value' => Type::INTEGER,
            'is_value_mutable' => Type::BOOLEAN,
            'is_repeatable' => Type::BOOLEAN,
        ];
    }
}
