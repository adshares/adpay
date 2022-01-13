<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;

final class HistoricalCpm
{
    private int $reportId;
    private Id $campaignId;
    private ?float $score;
    private int $maxCpm;
    private float $cpmFactor;
    private int $views;
    private int $viewsCost;
    private int $clicks;
    private int $clicksCost;
    private int $conversions;
    private int $conversionsCost;

    public function __construct(
        int $reportId,
        Id $campaignId,
        ?float $score,
        int $maxCpm,
        float $cpmFactor,
        int $views,
        int $viewsCost,
        int $clicks,
        int $clicksCost,
        int $conversions,
        int $conversionsCost
    ) {
        $this->reportId = $reportId;
        $this->campaignId = $campaignId;
        $this->score = $score;
        $this->maxCpm = $maxCpm;
        $this->cpmFactor = $cpmFactor;
        $this->views = $views;
        $this->viewsCost = $viewsCost;
        $this->clicks = $clicks;
        $this->clicksCost = $clicksCost;
        $this->conversions = $conversions;
        $this->conversionsCost = $conversionsCost;
    }

    public function getReportId(): int
    {
        return $this->reportId;
    }

    public function getCampaignId(): Id
    {
        return $this->campaignId;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getMaxCpm(): int
    {
        return $this->maxCpm;
    }

    public function getCpmFactor(): float
    {
        return $this->cpmFactor;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getViewsCost(): int
    {
        return $this->viewsCost;
    }

    public function getClicks(): int
    {
        return $this->clicks;
    }

    public function getClicksCost(): int
    {
        return $this->clicksCost;
    }

    public function getConversions(): int
    {
        return $this->conversions;
    }

    public function getConversionsCost(): int
    {
        return $this->conversionsCost;
    }
}
