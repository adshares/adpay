<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Type;

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
            'deleted_at' => $campaign->getDeletedAt(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'advertiser_id' => Type::BINARY,
            'time_start' => Type::DATETIME,
            'time_end' => Type::DATETIME,
            'filters' => Type::JSON,
            'budget' => Type::INTEGER,
            'max_cpm' => Type::INTEGER,
            'max_cpc' => Type::INTEGER,
            'deleted_at' => TYPE::DATETIME,
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
            DateTimeHelper::fromString($row['deleted_at'])
        );
    }
}
