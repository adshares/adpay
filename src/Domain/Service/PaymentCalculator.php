<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

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

            if ($status->isRejected()) {
                yield new Payment(
                    new EventType($event['type']),
                    new Id($event['id']),
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

                yield new Payment(
                    new EventType($event['type']),
                    new Id($event['id']),
                    PaymentStatus::createAccepted(),
                    $value
                );
            }
        }
    }

    private function validateEvent(array $event): PaymentStatus
    {
        $status = PaymentStatus::ACCEPTED;
        $isConversion = $event['type'] === EventType::CONVERSION;

        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$event['campaign_id']] ?? null;
        /** @var Banner $banner */
        $banner = $this->banners[$event['banner_id']] ?? null;
        /** @var Conversion $conversion */
        $conversion = $isConversion ? ($this->conversions[$event['conversion_id']] ?? null) : null;

        $eventTime = $event['time'];

        if ($campaign === null) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($campaign->getDeletedAt() !== null && $campaign->getDeletedAt()->getTimestamp() < $eventTime) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($banner === null) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($banner->getDeletedAt() !== null && $banner->getDeletedAt()->getTimestamp() < $eventTime) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($isConversion && $conversion === null) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif ($isConversion
            && $conversion->getDeletedAt() !== null
            && $conversion->getDeletedAt()->getTimestamp() < $eventTime) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif ($isConversion && $event['payment_status'] !== null) {
            $status = $event['payment_status'];
        } elseif ($campaign->getTimeStart()->getTimestamp() > $eventTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($campaign->getTimeEnd() !== null && $campaign->getTimeEnd()->getTimestamp() < $eventTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($event['human_score'] < $this->humanScoreThreshold) {
            $status = PaymentStatus::HUMAN_SCORE_TOO_LOW;
        } elseif (!$campaign->checkFilters($event['keywords'])) {
            $status = PaymentStatus::INVALID_TARGETING;
        }

        return new PaymentStatus($status);
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
