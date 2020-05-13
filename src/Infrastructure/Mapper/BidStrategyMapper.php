<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\ValueObject\Id;
use Doctrine\DBAL\Types\Type;

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
        ];
    }

    public static function types(): array
    {
        return [
            'bid_strategy_id' => Type::BINARY,
            'category' => Type::STRING,
            'rank' => Type::DECIMAL,
        ];
    }

    public static function fill(array $row): BidStrategy
    {
        return new BidStrategy(Id::fromBin($row['bid_strategy_id']), $row['category'], (float)$row['rank']);
    }
}
