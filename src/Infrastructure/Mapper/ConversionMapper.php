<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\Model\Conversion;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\LimitType;
use App\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Types;

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
            'id' => Types::BINARY,
            'campaign_id' => Types::BINARY,
            'limit_type' => Types::STRING,
            'value' => Types::INTEGER,
            'is_repeatable' => Types::BOOLEAN,
            'deleted_at' => Types::DATETIME_MUTABLE,
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
