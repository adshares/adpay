<?php

declare(strict_types=1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\ValueObject\BannerType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Type;

class BannerMapper
{
    public static function table(): string
    {
        return 'banners';
    }

    public static function map(Banner $banner): array
    {
        return [
            'id' => $banner->getId()->toBin(),
            'campaign_id' => $banner->getCampaignId()->toBin(),
            'size' => $banner->getSize(),
            'type' => $banner->getType()->toString(),
            'deleted_at' => $banner->getDeletedAt(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            'size' => Type::STRING,
            'type' => Type::STRING,
            'deleted_at' => TYPE::DATETIME,
        ];
    }

    public static function fill(array $row): Banner
    {
        return new Banner(
            Id::fromBin($row['id']),
            Id::fromBin($row['campaign_id']),
            $row['size'],
            new BannerType($row['type']),
            $row['deleted_at'] !== null ? DateTimeHelper::fromString($row['deleted_at']) : null
        );
    }
}
