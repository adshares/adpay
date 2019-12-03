<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Mapper;

use Adshares\AdPay\Domain\Model\Event;
use Doctrine\DBAL\Types\Type;

abstract class EventMapper
{
    abstract public static function table(): string;

    abstract protected static function getEventType(): string;

    public static function map(Event $event): array
    {
        return [
            'id' => $event->getId()->toBin(),
            'time' => $event->getTime(),
            'case_id' => $event->getCaseId()->toBin(),
            'case_time' => $event->getCaseTime(),
            'publisher_id' => $event->getPublisherId()->toBin(),
            'zone_id' => $event->getZoneId() !== null ? $event->getZoneId()->toBin() : null,
            'advertiser_id' => $event->getAdvertiserId()->toBin(),
            'campaign_id' => $event->getCampaignId()->toBin(),
            'banner_id' => $event->getBannerId()->toBin(),
            'impression_id' => $event->getImpressionId()->toBin(),
            'tracking_id' => $event->getTrackingId()->toBin(),
            'user_id' => $event->getUserId()->toBin(),
            'human_score' => $event->getHumanScore(),
            'page_rank' => $event->getPageRank(),
            'keywords' => $event->getKeywords(),
            'context' => $event->getContextData(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Type::BINARY,
            'time' => Type::DATETIME,
            'case_id' => Type::BINARY,
            'case_time' => Type::DATETIME,
            'publisher_id' => Type::BINARY,
            'zone_id' => Type::BINARY,
            'advertiser_id' => Type::BINARY,
            'campaign_id' => Type::BINARY,
            'banner_id' => Type::BINARY,
            'impression_id' => Type::BINARY,
            'tracking_id' => Type::BINARY,
            'user_id' => Type::BINARY,
            'human_score' => Type::FLOAT,
            'page_rank' => Type::FLOAT,
            'keywords' => Type::JSON,
            'context' => Type::JSON,
        ];
    }

    public static function fillRaw(array $row): array
    {
        return [
            'type' => static::getEventType(),
            'id' => bin2hex($row['id']),
            'time' => $row['time'],
            'case_id' => bin2hex($row['case_id']),
            'case_time' => $row['case_time'],
            'publisher_id' => bin2hex($row['publisher_id']),
            'zone_id' => $row['zone_id'] !== null ? bin2hex($row['zone_id']) : null,
            'advertiser_id' => bin2hex($row['advertiser_id']),
            'campaign_id' => bin2hex($row['campaign_id']),
            'banner_id' => bin2hex($row['banner_id']),
            'impression_id' => bin2hex($row['impression_id']),
            'tracking_id' => bin2hex($row['tracking_id']),
            'user_id' => bin2hex($row['user_id']),
            'human_score' => (float)$row['human_score'],
            'page_rank' => (float)$row['page_rank'],
            'keywords' => json_decode($row['keywords'], true),
            'context' => json_decode($row['context'], true),
        ];
    }
}
