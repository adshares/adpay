<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\BannerType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Size;
use DateTimeInterface;

final class Banner
{
    /** @var Id */
    private $id;

    /** @var Id */
    private $campaignId;

    /** @var Size */
    private $size;

    /** @var BannerType */
    private $type;

    /** @var DateTimeInterface|null */
    private $deletedAt;

    public function __construct(Id $id, Id $campaignId, Size $size, BannerType $type)
    {
        $this->id = $id;
        $this->campaignId = $campaignId;
        $this->size = $size;
        $this->type = $type;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getCampaignId(): Id
    {
        return $this->campaignId;
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function getType(): BannerType
    {
        return $this->type;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }
}
