<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Types;

class BidStrategyMapper
{
    public static function table(): string
    {
        return 'bid_strategy_details';
    }

    public static function map(BidStrategy $bidStrategy): array
    {
        return [
            'bid_strategy_id' => $bidStrategy->getId()->toBin(),
            'category' => $bidStrategy->getCategory(),
            'rank' => $bidStrategy->getRank(),
            'deleted_at' => $bidStrategy->getDeletedAt(),
        ];
    }

    public static function types(): array
    {
        return [
            'bid_strategy_id' => Types::BINARY,
            'category' => Types::STRING,
            'rank' => Types::DECIMAL,
            'deleted_at' => Types::DATETIME_MUTABLE,
        ];
    }

    public static function fill(array $row): BidStrategy
    {
        return new BidStrategy(
            Id::fromBin($row['bid_strategy_id']),
            $row['category'],
            (float)$row['rank'],
            $row['deleted_at'] !== null ? DateTimeHelper::fromString($row['deleted_at']) : null
        );
    }
}
