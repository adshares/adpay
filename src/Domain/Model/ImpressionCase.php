<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\Id;
use DateTimeInterface;

final class ImpressionCase
{
    private Id $id;

    private DateTimeInterface $time;

    private Id $publisherId;

    private ?Id $zoneId;

    private Id $advertiserId;

    private Id $campaignId;

    private Id $bannerId;

    private Impression $impression;

    public function __construct(
        Id $id,
        DateTimeInterface $time,
        Id $publisherId,
        ?Id $zoneId,
        Id $advertiserId,
        Id $campaignId,
        Id $bannerId,
        Impression $impression
    ) {
        $this->id = $id;
        $this->time = $time;
        $this->publisherId = $publisherId;
        $this->zoneId = $zoneId;
        $this->advertiserId = $advertiserId;
        $this->campaignId = $campaignId;
        $this->bannerId = $bannerId;
        $this->impression = $impression;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getTime(): DateTimeInterface
    {
        return $this->time;
    }

    public function getPublisherId(): Id
    {
        return $this->publisherId;
    }

    public function getZoneId(): ?Id
    {
        return $this->zoneId;
    }

    public function getAdvertiserId(): Id
    {
        return $this->advertiserId;
    }

    public function getCampaignId(): Id
    {
        return $this->campaignId;
    }

    public function getBannerId(): Id
    {
        return $this->bannerId;
    }

    public function getImpression(): Impression
    {
        return $this->impression;
    }

    public function getImpressionId(): Id
    {
        return $this->impression->getId();
    }

    public function getTrackingId(): Id
    {
        return $this->impression->getTrackingId();
    }

    public function getUserId(): Id
    {
        return $this->impression->getUserId();
    }

    public function getContext(): Context
    {
        return $this->impression->getContext();
    }

    public function getContextData(): array
    {
        return $this->impression->getContextData();
    }

    public function getKeywords(): array
    {
        return $this->impression->getKeywords();
    }

    public function getHumanScore(): float
    {
        return $this->impression->getHumanScore();
    }

    public function getPageRank(): float
    {
        return $this->impression->getPageRank();
    }

    public function getAdsTxt(): ?int
    {
        return $this->impression->getAdsTxt();
    }
}
