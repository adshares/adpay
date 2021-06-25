<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Type;

class ConversionMapper
{
    public static function table(): string
    {
        return 'conversions';
    }

    public static function map(Conversion $conversion): array
    {
        return [
            'id' => $conversion->getId()->toBin(),
            'campaign_id' => $conversion->getCampaignId()->toBin(),
            'limit_type' => $conversion->getLimitType()->toString(),
            'is_repeatable' => $conversion->isRepeatable(),
            'deleted_at' => $conversion->getDeletedAt(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            'limit_type' => Type::STRING,
            'value' => Type::INTEGER,
            'is_repeatable' => Type::BOOLEAN,
            'deleted_at' => TYPE::DATETIME,
        ];
    }

    public static function fill(array $row): Conversion
    {
        return new Conversion(
            Id::fromBin($row['id']),
            Id::fromBin($row['campaign_id']),
            new LimitType($row['limit_type']),
            (bool)$row['is_repeatable'],
            $row['deleted_at'] !== null ? DateTimeHelper::fromString($row['deleted_at']) : null
        );
    }
}
