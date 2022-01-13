<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\CampaignCost;
use Adshares\AdPay\Domain\ValueObject\Id;
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
            'cpm_factor' => $campaignCost->getMaxCpm(),
            'view' => $campaignCost->getViews(),
            'view_cost' => $campaignCost->getViewsCost(),
            'click' => $campaignCost->getClicks(),
            'click_cost' => $campaignCost->getClicksCost(),
            'conversion' => $campaignCost->getConversions(),
            'conversion_cost' => $campaignCost->getConversionsCost(),
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
            'view' => Types::INTEGER,
            'view_cost' => Types::INTEGER,
            'click' => Types::INTEGER,
            'click_cost' => Types::INTEGER,
            'conversion' => Types::INTEGER,
            'conversion_cost' => Types::INTEGER,
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
            (int)$row['view'],
            (int)$row['view_cost'],
            (int)$row['click'],
            (int)$row['click_cost'],
            (int)$row['conversion'],
            (int)$row['conversion_cost'],
        );
    }
}
