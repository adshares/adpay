<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\LimitType;
use DateTimeInterface;

final class Conversion
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $campaignId;

    /** @var LimitType */
    private $limitType;

    /** @var bool */
    private $repeatable;

    /** @var DateTimeInterface|null */
    private $deletedAt;

    public function __construct(
        Id $id,
        Id $campaignId,
        LimitType $limitType,
        bool $repeatable = false,
        DateTimeInterface $deletedAt = null
    ) {
        $this->id = $id;
        $this->campaignId = $campaignId;
        $this->limitType = $limitType;
        $this->repeatable = $repeatable;
        $this->deletedAt = $deletedAt;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getCampaignId(): Id
    {
        return $this->campaignId;
    }

    public function getLimitType(): LimitType
    {
        return $this->limitType;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}
