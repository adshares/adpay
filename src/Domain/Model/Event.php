<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use DateTimeInterface;

abstract class Event
{
    /** @var Id */
    private $id;

    /** @var EventType */
    private $type;

    /** @var DateTimeInterface */
    private $time;

    /** @var ImpressionCase */
    private $case;

    public function __construct(
        Id $id,
        EventType $type,
        DateTimeInterface $time,
        ImpressionCase $case
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->time = $time;
        $this->case = $case;

        if ($time < $case->getTime()) {
            throw InvalidArgumentException::fromArgument(
                'time',
                $time->format(DateTimeInterface::ATOM),
                'The time must be greater than or equal to the case time'
            );
        }
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getType(): EventType
    {
        return $this->type;
    }

    public function getTime(): DateTimeInterface
    {
        return $this->time;
    }

    public function getCase(): ImpressionCase
    {
        return $this->case;
    }

    public function getCaseId(): Id
    {
        return $this->case->getId();
    }

    public function getCaseTime(): DateTimeInterface
    {
        return $this->case->getTime();
    }

    public function getPublisherId(): Id
    {
        return $this->case->getPublisherId();
    }

    public function getZoneId(): ?Id
    {
        return $this->case->getZoneId();
    }

    public function getAdvertiserId(): Id
    {
        return $this->case->getAdvertiserId();
    }

    public function getCampaignId(): Id
    {
        return $this->case->getCampaignId();
    }

    public function getBannerId(): Id
    {
        return $this->case->getBannerId();
    }

    public function getImpression(): Impression
    {
        return $this->case->getImpression();
    }

    public function getImpressionId(): Id
    {
        return $this->case->getImpressionId();
    }

    public function getTrackingId(): Id
    {
        return $this->case->getTrackingId();
    }

    public function getUserId(): Id
    {
        return $this->case->getUserId();
    }

    public function getContext(): Context
    {
        return $this->case->getContext();
    }

    public function getContextData(): array
    {
        return $this->case->getContextData();
    }

    public function getKeywords(): array
    {
        return $this->case->getKeywords();
    }

    public function getHumanScore(): float
    {
        return $this->case->getHumanScore();
    }

    public function getPageRank(): float
    {
        return $this->case->getPageRank();
    }
}
