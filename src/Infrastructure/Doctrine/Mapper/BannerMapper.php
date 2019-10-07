<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Mapper;

use Adshares\AdPay\Domain\Model\Banner;
use Doctrine\DBAL\Types\Type;

class BannerMapper
{
    public static function map(Banner $banner): array
    {
        return [
            'id' => $banner->getId()->toBin(),
            'campaign_id' => $banner->getCampaignId()->toBin(),
            'size' => $banner->getSize()->toString(),
            'type' => $banner->getType()->toString(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            'size' => Type::STRING,
            'type' => Type::STRING,
        ];
    }
}