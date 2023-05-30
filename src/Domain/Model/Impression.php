<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\Id;

final class Impression
{
    private Id $id;

    private Id $trackingId;

    private Id $userId;

    private Context $context;

    public function __construct(
        Id $id,
        Id $trackingId,
        Id $userId,
        Context $context
    ) {
        $this->id = $id;
        $this->trackingId = $trackingId;
        $this->userId = $userId;
        $this->context = $context;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getTrackingId(): Id
    {
        return $this->trackingId;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getContextData(): array
    {
        return $this->context->getData();
    }

    public function getKeywords(): array
    {
        return $this->context->getKeywords();
    }

    public function getHumanScore(): float
    {
        return $this->context->getHumanScore();
    }

    public function getPageRank(): float
    {
        return $this->context->getPageRank();
    }

    public function getAdsTxt(): ?int
    {
        return $this->context->getAdsTxt();
    }
}
