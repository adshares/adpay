<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\Event;
use Doctrine\DBAL\Types\Type;

abstract class EventMapper
{
    abstract public static function table(): string;

    public static function map(Event $event): array
    {
        return [
            'id' => $event->getId()->toBin(),
            'time' => $event->getTime(),
            'payment_status' => $event->getPaymentStatus()->getStatus(),
            'case_id' => $event->getCaseId()->toBin(),
            'publisher_id' => $event->getPublisherId()->toBin(),
            'zone_id' => $event->getZoneId() !== null ? $event->getZoneId()->toBin() : null,
            'advertiser_id' => $event->getAdvertiserId()->toBin(),
            'campaign_id' => $event->getCampaignId()->toBin(),
            'banner_id' => $event->getBannerId()->toBin(),
            'impression_id' => $event->getImpressionId()->toBin(),
            'tracking_id' => $event->getTrackingId()->toBin(),
            'user_id' => $event->getUserId()->toBin(),
            'human_score' => $event->getHumanScore(),
            'keywords' => $event->getKeywords(),
            'context' => $event->getContextData(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'time' => Type::DATETIME,
            'payment_status' => Type::INTEGER,
            'case_id' => Type::BINARY,
            'publisher_id' => Type::BINARY,
            'zone_id' => Type::BINARY,
            'advertiser_id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            'banner_id' => Type::BINARY,
            'impression_id' => Type::BINARY,
            'tracking_id' => Type::BINARY,
            'user_id' => Type::BINARY,
            'human_score' => Type::FLOAT,
            'keywords' => Type::JSON,
            'context' => Type::JSON,
        ];
    }
}
