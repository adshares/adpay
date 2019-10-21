<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;

final class PaymentCalculator
{
    /** @var float */
    private $humanScoreThreshold = 0.5;

    /** @var array */
    private $campaigns = [];

    /** @var array */
    private $banners = [];

    /** @var array */
    private $conversions = [];

    public function __construct(CampaignCollection $campaigns, array $config = [])
    {
        $this->humanScoreThreshold = $config['humanScoreThreshold'] ?? $this->humanScoreThreshold;

        foreach ($campaigns as $campaign) {
            /** @var Campaign $campaign */
            $this->campaigns[$campaign->getId()->toString()] = $campaign;
            foreach ($campaign->getBanners() as $banner) {
                /** @var Banner $banner */
                $this->banners[$banner->getId()->toString()] = $banner;
            }
            foreach ($campaign->getConversions() as $conversion) {
                /** @var Conversion $conversion */
                $this->conversions[$conversion->getId()->toString()] = $conversion;
            }
        }
    }

    public function calculate(
        iterable $events
    ): iterable {
        $matrix = [];
        foreach ($events as $event) {
            $status = $this->validateEvent($event);

            if ($status !== PaymentStatus::ACCEPTED) {
                yield self::createPayment(
                    $event['type'],
                    $event['id'],
                    $status
                );
                continue;
            }

            $campaignId = $event['campaign_id'];
            $userId = $event['user_id'];

            /** @var Campaign $campaign */
            $campaign = $this->campaigns[$campaignId];

            if (!array_key_exists($campaignId, $matrix)) {
                $matrix[$campaignId] = [
                    'events' => [],
                    EventType::VIEW => [],
                    EventType::CLICK => [],
                    EventType::CONVERSION => [],
                    'costs' => 0,
                ];
            }

            $matrix[$campaignId]['events'][] = $event;
            if (!array_key_exists($userId, $matrix[$campaignId][$event['type']])) {
                $matrix[$campaignId][$event['type']][$userId] = 1;
                $matrix[$campaignId]['costs'] += self::getEventCost($campaign, $event);
            } else {
                $matrix[$campaignId][$event['type']][$userId]++;
            }
        }

        foreach ($matrix as $campaignId => $item) {
            /** @var Campaign $campaign */
            $campaign = $this->campaigns[$campaignId];
            $factor = $item['costs'] > $campaign->getBudgetValue() ? $campaign->getBudgetValue() / $item['costs'] : 1;

            foreach ($item['events'] as $event) {
                $value = self::getEventCost($campaign, $event) * $factor;
                $value = (int)($value / $item[$event['type']][$event['user_id']]);

                yield self::createPayment(
                    $event['type'],
                    $event['id'],
                    PaymentStatus::ACCEPTED,
                    $value
                );
            }
        }
    }

    private function validateEvent(array $event): int
    {
        $status = PaymentStatus::ACCEPTED;
        $isConversion = $event['type'] === EventType::CONVERSION;

        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$event['campaign_id']] ?? null;
        /** @var Banner $banner */
        $banner = $this->banners[$event['banner_id']] ?? null;
        /** @var Conversion $conversion */
        $conversion = $isConversion ? ($this->conversions[$event['conversion_id']] ?? null) : null;

        $eventTime = DateTimeHelper::fromString($event['time']);

        if ($campaign === null) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($campaign->getDeletedAt() !== null && $campaign->getDeletedAt() < $eventTime) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($banner === null) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($banner->getDeletedAt() !== null && $banner->getDeletedAt() < $eventTime) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($isConversion && $conversion === null) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif ($isConversion
            && $conversion->getDeletedAt() !== null
            && $conversion->getDeletedAt() < $eventTime) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif ($isConversion && $event['payment_status'] !== null) {
            $status = $event['payment_status'];
        } elseif ($campaign->getTimeStart() > $eventTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($campaign->getTimeEnd() !== null && $campaign->getTimeEnd() < $eventTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($event['human_score'] < $this->humanScoreThreshold) {
            $status = PaymentStatus::HUMAN_SCORE_TOO_LOW;
        } elseif (!$campaign->checkFilters($event['keywords'])) {
            $status = PaymentStatus::INVALID_TARGETING;
        }

        return $status;
    }

    private static function createPayment(string $eventType, string $eventId, int $status, ?int $value = null)
    {
        return [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'status' => $status,
            'value' => $value,
        ];
    }

    private static function getEventCost(Campaign $campaign, array $event): int
    {
        switch ($event['type']) {
            case EventType::CLICK:
                return (int)($campaign->getClickCost() * $event['page_rank']);
            case EventType::CONVERSION:
                return $event['conversion_value'];
            default:
                return (int)($campaign->getViewCost() * $event['page_rank']);
        }
    }
}
