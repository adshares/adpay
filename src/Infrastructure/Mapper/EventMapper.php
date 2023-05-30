<?php

declare(strict_types=1);

namespace App\Infrastructure\Mapper;

use App\Domain\Model\Event;
use Doctrine\DBAL\Types\Types;

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
            'ads_txt' => $event->getAdsTxt(),
            'keywords' => $event->getKeywords(),
            'context' => $event->getContextData(),
        ];
    }

    public static function types(): array
    {
        return [
            'id' => Types::BINARY,
            'time' => Types::DATETIME_MUTABLE,
            'case_id' => Types::BINARY,
            'case_time' => Types::DATETIME_MUTABLE,
            'publisher_id' => Types::BINARY,
            'zone_id' => Types::BINARY,
            'advertiser_id' => Types::BINARY,
            'campaign_id' => Types::BINARY,
            'banner_id' => Types::BINARY,
            'impression_id' => Types::BINARY,
            'tracking_id' => Types::BINARY,
            'user_id' => Types::BINARY,
            'human_score' => Types::FLOAT,
            'page_rank' => Types::FLOAT,
            'ads_txt' => Types::INTEGER,
            'keywords' => Types::JSON,
            'context' => Types::JSON,
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
            'ads_txt' => $row['ads_txt'],
            'keywords' => json_decode($row['keywords'], true),
            'context' => json_decode($row['context'], true),
        ];
    }
}
