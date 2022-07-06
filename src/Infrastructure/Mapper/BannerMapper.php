<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\Model\Banner;
use App\Domain\ValueObject\BannerType;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use Doctrine\DBAL\Types\Types;

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
            'id' => Types::BINARY,
            'campaign_id' => Types::BINARY,
            'size' => Types::STRING,
            'type' => Types::STRING,
            'deleted_at' => Types::DATETIME_MUTABLE,
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
