<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Id;

final class CampaignCost
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
        ?float $score = null,
        int $maxCpm = 0,
        float $cpmFactor = 1.0,
        int $views = 0,
        int $viewsCost = 0,
        int $clicks = 0,
        int $clicksCost = 0,
        int $conversions = 0,
        int $conversionsCost = 0
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

    public function setScore(?float $score): void
    {
        $this->score = $score;
    }

    public function getMaxCpm(): int
    {
        return $this->maxCpm;
    }

    public function setMaxCpm(int $maxCpm): void
    {
        $this->maxCpm = $maxCpm;
    }

    public function getCpmFactor(): float
    {
        return $this->cpmFactor;
    }

    public function setCpmFactor(float $cpmFactor): void
    {
        $this->cpmFactor = $cpmFactor;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getViewsCost(): int
    {
        return $this->viewsCost;
    }

    public function setViewsCost(int $viewsCost): void
    {
        $this->viewsCost = $viewsCost;
    }

    public function getClicks(): int
    {
        return $this->clicks;
    }

    public function setClicks(int $clicks): void
    {
        $this->clicks = $clicks;
    }

    public function getClicksCost(): int
    {
        return $this->clicksCost;
    }

    public function setClicksCost(int $clicksCost): void
    {
        $this->clicksCost = $clicksCost;
    }

    public function getConversions(): int
    {
        return $this->conversions;
    }

    public function setConversions(int $conversions): void
    {
        $this->conversions = $conversions;
    }

    public function getConversionsCost(): int
    {
        return $this->conversionsCost;
    }

    public function setConversionsCost(int $conversionsCost): void
    {
        $this->conversionsCost = $conversionsCost;
    }

}
