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
        $this->humanScoreThreshold = (float)($config['humanScoreThreshold'] ?? $this->humanScoreThreshold);

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

    public function calculate(iterable $events): iterable
    {
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

            $this->fillMatrix($matrix, $event);
        }

        foreach ($matrix as $campaignId => $item) {
            /** @var Campaign $campaign */
            $campaign = $this->campaigns[$campaignId];
            $factor = $item['costs'] > $campaign->getBudgetValue() ? $campaign->getBudgetValue() / $item['costs'] : 1;

            foreach ($item['events'] as $event) {
                $value = $this->getEventCost($event, $factor, $item[$event['type']][$event['user_id']] ?? 1);
                yield self::createPayment(
                    $event['type'],
                    $event['id'],
                    PaymentStatus::ACCEPTED,
                    (int)$value
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

    private function getEventCost(array $event, float $factor = 1.0, int $userCount = 1): float
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$event['campaign_id']];
        $value = 0;

        if ($event['type'] === EventType::CONVERSION) {
            /** @var Conversion $conversion */
            $conversion = $this->conversions[$event['conversion_id']];
            $value = $event['conversion_value'];
            if ($conversion->getLimitType()->isInBudget()) {
                $value = $value * $factor;
            }
        } elseif ($event['type'] === EventType::CLICK) {
            $value = $campaign->getClickCost() * $event['page_rank'] * $factor / $userCount;
        } elseif ($event['type'] === EventType::VIEW) {
            $value = $campaign->getViewCost() * $event['page_rank'] * $factor / $userCount;
        }

        return $value;
    }

    private function fillMatrix(array &$matrix, array $event): void
    {
        $campaignId = $event['campaign_id'];
        $userId = $event['user_id'];

        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$campaignId];

        if (!array_key_exists($campaignId, $matrix)) {
            $matrix[$campaignId] = [
                'events' => [],
                'conversions' => [],
                EventType::VIEW => [],
                EventType::CLICK => [],
                'costs' => 0,
            ];
        }

        if ($event['type'] === EventType::CONVERSION) {
            $conversionId = $event['conversion_id'];
            /** @var Conversion $conversion */
            $conversion = $this->conversions[$conversionId];

            if (!array_key_exists($conversionId, $matrix[$campaignId]['conversions'])) {
                $matrix[$campaignId]['conversions'][$conversionId] = [
                    'cost' => $conversion->getCost(),
                    'groups' => [],
                ];
            }

            if (!$conversion->isRepeatable()) {
                if (!array_key_exists($userId, $matrix[$campaignId]['conversions'][$conversionId]['groups'])) {
                    $matrix[$campaignId]['conversions'][$conversionId]['groups'][$userId] = $event['group_id'];
                }
                if ($matrix[$campaignId]['conversions'][$conversionId]['groups'][$userId] !== $event['group_id']) {
                    $event['conversion_value'] = 0;
                }
            }

            if ($conversion->getLimitValue() !== null) {
                $cost = $matrix[$campaignId]['conversions'][$conversionId]['cost'];
                if ($cost + $event['conversion_value'] > $conversion->getLimitValue()) {
                    $event['conversion_value'] = max(0, $conversion->getLimitValue() - $cost);
                }
                $matrix[$campaignId]['conversions'][$conversionId]['cost'] = $cost + $event['conversion_value'];
            }

            if ($conversion->getLimitType()->isInBudget()) {
                $matrix[$campaignId]['costs'] += $event['conversion_value'];
            }
        } else {
            if (!array_key_exists($userId, $matrix[$campaignId][$event['type']])) {
                $matrix[$campaignId][$event['type']][$userId] = 1;
                $matrix[$campaignId]['costs'] += $this->getEventCost($event);
            } else {
                $matrix[$campaignId][$event['type']][$userId]++;
            }
        }

        $matrix[$campaignId]['events'][] = $event;
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
}