<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\Model\BannerCollection;
use App\Domain\Model\Campaign;
use App\Domain\Model\ConversionCollection;
use App\Domain\ValueObject\Budget;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Types;

class CampaignMapper
{
    public static function table(): string
    {
        return 'campaigns';
    }

    public static function map(Campaign $campaign): array
    {
        return [
            'id' => $campaign->getId()->toBin(),
            'advertiser_id' => $campaign->getAdvertiserId()->toBin(),
            'time_start' => $campaign->getTimeStart(),
            'time_end' => $campaign->getTimeEnd(),
            'filters' => $campaign->getFilters(),
            'budget' => $campaign->getBudgetValue(),
            'max_cpm' => $campaign->getMaxCpm(),
            'max_cpc' => $campaign->getMaxCpc(),
            'bid_strategy_id' => $campaign->getBidStrategyId()->toBin(),
            'deleted_at' => $campaign->getDeletedAt(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Types::BINARY,
            'advertiser_id' => Types::BINARY,
            'time_start' => Types::DATETIME_MUTABLE,
            'time_end' => Types::DATETIME_MUTABLE,
            'filters' => Types::JSON,
            'budget' => Types::INTEGER,
            'max_cpm' => Types::INTEGER,
            'max_cpc' => Types::INTEGER,
            'bid_strategy_id' => Types::BINARY,
            'deleted_at' => Types::DATETIME_MUTABLE,
        ];
    }

    public static function fill(array $row, BannerCollection $banners, ConversionCollection $conversions): Campaign
    {
        $budget = new Budget(
            (int)$row['budget'],
            $row['max_cpm'] !== null ? (int)$row['max_cpm'] : null,
            $row['max_cpc'] !== null ? (int)$row['max_cpc'] : null
        );

        return new Campaign(
            Id::fromBin($row['id']),
            Id::fromBin($row['advertiser_id']),
            DateTimeHelper::fromString($row['time_start']),
            $row['time_end'] !== null ? DateTimeHelper::fromString($row['time_end']) : null,
            $budget,
            $banners,
            json_decode($row['filters'], true),
            $conversions,
            Id::fromBin($row['bid_strategy_id']),
            $row['deleted_at'] !== null ? DateTimeHelper::fromString($row['deleted_at']) : null
        );
    }
}
