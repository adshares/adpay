<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\Id;

final class ImpressionCase
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $publisherId;

    /** @var ?Id */
    private $zoneId;

    /** @var Id */
    private $advertiserId;

    /** @var Id */
    private $campaignId;

    /** @var Id */
    private $bannerId;

    /** @var Impression */
    private $impression;

    public function __construct(
        Id $id,
        Id $publisherId,
        ?Id $zoneId,
        Id $advertiserId,
        Id $campaignId,
        Id $bannerId,
        Impression $impression
    ) {
        $this->id = $id;
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
}
