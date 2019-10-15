<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\Campaign;
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
}
