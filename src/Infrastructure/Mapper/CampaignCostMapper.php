<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\Model\CampaignCost;
use App\Domain\ValueObject\Id;
use Doctrine\DBAL\Types\Types;

class CampaignCostMapper
{
    public static function table(): string
    {
        return 'campaign_costs';
    }

    public static function map(CampaignCost $campaignCost): array
    {
        return [
            'report_id' => $campaignCost->getReportId(),
            'campaign_id' => $campaignCost->getCampaignId()->toBin(),
            'score' => $campaignCost->getScore(),
            'max_cpm' => $campaignCost->getMaxCpm(),
            'cpm_factor' => $campaignCost->getCpmFactor(),
            'views' => $campaignCost->getViews(),
            'views_cost' => $campaignCost->getViewsCost(),
            'clicks' => $campaignCost->getClicks(),
            'clicks_cost' => $campaignCost->getClicksCost(),
            'conversions' => $campaignCost->getConversions(),
            'conversions_cost' => $campaignCost->getConversionsCost(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Types::INTEGER,
            'report_id' => Types::INTEGER,
            'campaign_id' => Types::BINARY,
            'score' => Types::FLOAT,
            'max_cpm' => Types::INTEGER,
            'cpm_factor' => Types::FLOAT,
            'views' => Types::INTEGER,
            'views_cost' => Types::INTEGER,
            'clicks' => Types::INTEGER,
            'clicks_cost' => Types::INTEGER,
            'conversions' => Types::INTEGER,
            'conversions_cost' => Types::INTEGER,
        ];
    }

    public static function fill(array $row): CampaignCost
    {
        return new CampaignCost(
            (int)$row['report_id'],
            Id::fromBin($row['campaign_id']),
            isset($row['score']) ? (float)$row['score'] : null,
            (int)$row['max_cpm'],
            (float)$row['cpm_factor'],
            (int)$row['views'],
            (int)$row['views_cost'],
            (int)$row['clicks'],
            (int)$row['clicks_cost'],
            (int)$row['conversions'],
            (int)$row['conversions_cost'],
        );
    }
}
