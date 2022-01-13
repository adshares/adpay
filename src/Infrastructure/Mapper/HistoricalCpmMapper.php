<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\HistoricalCpm;
use Adshares\AdPay\Domain\ValueObject\Id;
use Doctrine\DBAL\Types\Types;

class HistoricalCpmMapper
{
    public static function table(): string
    {
        return 'campaign_costs';
    }

    public static function map(HistoricalCpm $historicalCpm): array
    {
        return [
            'report_id' => $historicalCpm->getReportId(),
            'campaign_id' => $historicalCpm->getCampaignId()->toBin(),
            'score' => $historicalCpm->getScore(),
            'max_cpm' => $historicalCpm->getMaxCpm(),
            'cpm_factor' => $historicalCpm->getMaxCpm(),
            'view' => $historicalCpm->getViews(),
            'view_cost' => $historicalCpm->getViewsCost(),
            'click' => $historicalCpm->getClicks(),
            'click_cost' => $historicalCpm->getClicksCost(),
            'conversion' => $historicalCpm->getConversions(),
            'conversion_cost' => $historicalCpm->getConversionsCost(),
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

    public static function fill(array $row): HistoricalCpm
    {
        return new HistoricalCpm(
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
