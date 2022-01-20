<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Service;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\Model\BidStrategyCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\CampaignCost;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\CampaignCostCollection;
use Adshares\AdPay\Domain\Repository\CampaignCostRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentCalculatorConfig;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;

class PaymentCalculator
{
    private const CPM_INCREASING_FACTOR = 1.1;

    private const CPM_DECREASING_FACTOR = 0.9;

    private int $reportId;

    private CampaignCostRepository $campaignCostRepository;

    private PaymentCalculatorConfig $config;

    private array $bidStrategies = [];

    private array $bidStrategiesForCampaigns = [];

    private array $campaigns = [];

    private array $banners = [];

    private array $conversions = [];

    private array $viewCostByCampaignId = [];

    private array $campaignCosts = [];

    public function __construct(
        CampaignCollection $campaigns,
        BidStrategyCollection $bidStrategies,
        CampaignCostRepository $campaignCostRepository,
        PaymentCalculatorConfig $config
    ) {
        $this->campaignCostRepository = $campaignCostRepository;
        $this->config = $config;

        foreach ($bidStrategies as $bidStrategy) {
            /** @var BidStrategy $bidStrategy */
            $category = $bidStrategy->getCategory();
            $lastSeparatorIndex = strrpos($category, ':');
            $value = substr($category, 1 + $lastSeparatorIndex);
            $category = substr($category, 0, $lastSeparatorIndex);
            $this->bidStrategies[$bidStrategy->getId()->toString()][$category][$value] = $bidStrategy->getRank();
        }

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

    public function calculate(int $reportId, iterable $events): iterable
    {
        $this->reportId = $reportId;
        $this->campaignCosts = [];
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
        $this->fillNonConversionCosts($matrix);

        foreach ($matrix as $campaignId => $item) {
            /** @var Campaign $campaign */
            $campaign = $this->campaigns[$campaignId];
            $uniqueViewCount = count($item[EventType::VIEW]);
            $avgViewCost = $uniqueViewCount > 0 ? $item['costs_' . EventType::VIEW] / $uniqueViewCount : 0;
            $cpmScale = $avgViewCost > 0 ? $this->viewCostByCampaignId[$campaignId] / $avgViewCost : 1;
            $scaledCosts = $item['costs'] + $item['costs_' . EventType::VIEW] * ($cpmScale - 1);
            $factor = $scaledCosts > $campaign->getBudgetValue() ? $campaign->getBudgetValue() / $scaledCosts : 1;

            $costs = [
                EventType::VIEW => 0,
                EventType::CLICK => 0,
                EventType::CONVERSION => 0,
            ];
            foreach ($item['events'] as $event) {
                $value =
                    (int)$this->getEventCost($event, $factor, $item[$event['type']][$event['user_id']] ?? 1, $cpmScale);
                $costs[$event['type']] += $value;

                yield self::createPayment(
                    $event['type'],
                    $event['id'],
                    PaymentStatus::ACCEPTED,
                    $value
                );
            }

            $this->campaignCosts[$campaignId]->setViews(count($item[EventType::VIEW]));
            $this->campaignCosts[$campaignId]->setClicks(count($item[EventType::CLICK]));
            $this->campaignCosts[$campaignId]->setConversions(count($item[EventType::CONVERSION]));
            $this->campaignCosts[$campaignId]->setViewsCost($costs[EventType::VIEW]);
            $this->campaignCosts[$campaignId]->setClicksCost($costs[EventType::CLICK]);
            $this->campaignCosts[$campaignId]->setConversionsCost($costs[EventType::CONVERSION]);
        }

        $this->storeCampaignCosts();
    }

    private function validateEvent(array $event): int
    {
        $status = PaymentStatus::ACCEPTED;
        $isConversion = $event['type'] === EventType::CONVERSION;
        $humanScoreThreshold =
            $isConversion ? $this->config->getConversionHumanScoreThreshold() : $this->config->getHumanScoreThreshold();

        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$event['campaign_id']] ?? null;
        /** @var Banner $banner */
        $banner = $this->banners[$event['banner_id']] ?? null;
        /** @var Conversion $conversion */
        $conversion = $isConversion ? ($this->conversions[$event['conversion_id']] ?? null) : null;

        $caseTime = DateTimeHelper::fromString($event['case_time']);

        if ($campaign === null) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($campaign->getDeletedAt() !== null && $campaign->getDeletedAt() < $caseTime) {
            $status = PaymentStatus::CAMPAIGN_NOT_FOUND;
        } elseif ($banner === null) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($banner->getDeletedAt() !== null && $banner->getDeletedAt() < $caseTime) {
            $status = PaymentStatus::BANNER_NOT_FOUND;
        } elseif ($isConversion && $conversion === null) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif (
            $isConversion
            && $conversion->getDeletedAt() !== null
            && $conversion->getDeletedAt() < $caseTime
        ) {
            $status = PaymentStatus::CONVERSION_NOT_FOUND;
        } elseif (
            $isConversion
            && in_array($event['payment_status'], [PaymentStatus::CAMPAIGN_OUTDATED, PaymentStatus::INVALID_TARGETING])
        ) {
            $status = $event['payment_status'];
        } elseif ($campaign->getTimeStart() > $caseTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($campaign->getTimeEnd() !== null && $campaign->getTimeEnd() < $caseTime) {
            $status = PaymentStatus::CAMPAIGN_OUTDATED;
        } elseif ($event['human_score'] < $humanScoreThreshold) {
            $status = PaymentStatus::HUMAN_SCORE_TOO_LOW;
        } elseif (!$campaign->checkFilters($event['keywords'])) {
            $status = PaymentStatus::INVALID_TARGETING;
        }

        return $status;
    }

    private function getEventCost(array $event, float $factor = 1.0, int $userCount = 1, float $cpmScale = 1.0): float
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$event['campaign_id']];
        $value = 0;
        $pageRank = min(1, max(0, $event['page_rank']));

        if ($event['type'] === EventType::CONVERSION) {
            /** @var Conversion $conversion */
            $conversion = $this->conversions[$event['conversion_id']];
            $value = $event['conversion_value'];
            if ($conversion->getLimitType()->isInBudget()) {
                $value = $value * $factor;
            }
        } elseif ($event['type'] === EventType::CLICK) {
            $value =
                $campaign->getClickCost()
                * $pageRank
                * $this->getBidStrategyRank($campaign, $event)
                * $factor
                / $userCount;
        } elseif ($event['type'] === EventType::VIEW) {
            $value =
                $this->viewCostByCampaignId[$event['campaign_id']]
                * $pageRank
                * $this->getBidStrategyRank($campaign, $event)
                * $factor
                * $cpmScale
                / $userCount;
        }

        return $value;
    }

    private function fillMatrix(array &$matrix, array $event): void
    {
        $campaignId = $event['campaign_id'];
        $userId = $event['user_id'];

        if (!array_key_exists($campaignId, $matrix)) {
            $matrix[$campaignId] = [
                'events' => [],
                EventType::CONVERSION => [],
                EventType::VIEW => [],
                EventType::CLICK => [],
                'costs' => 0,
                'costs_' . EventType::VIEW => 0,
                'costs_' . EventType::CLICK => 0,
            ];
        }

        if ($event['type'] === EventType::CONVERSION) {
            $conversionId = $event['conversion_id'];
            /** @var Conversion $conversion */
            $conversion = $this->conversions[$conversionId];

            if (!array_key_exists($conversionId, $matrix[$campaignId][EventType::CONVERSION])) {
                $matrix[$campaignId][EventType::CONVERSION][$conversionId] = [];
            }

            if (!$conversion->isRepeatable()) {
                if (!array_key_exists($userId, $matrix[$campaignId][EventType::CONVERSION][$conversionId])) {
                    $matrix[$campaignId][EventType::CONVERSION][$conversionId][$userId] = $event['group_id'];
                }
                if ($matrix[$campaignId][EventType::CONVERSION][$conversionId][$userId] !== $event['group_id']) {
                    $event['conversion_value'] = 0;
                }
            }

            if ($conversion->getLimitType()->isInBudget()) {
                $matrix[$campaignId]['costs'] += $event['conversion_value'];
            }
        } else {
            if (!array_key_exists($userId, $matrix[$campaignId][$event['type']])) {
                $matrix[$campaignId][$event['type']][$userId] = 1;
            } else {
                $matrix[$campaignId][$event['type']][$userId]++;
            }
        }

        $matrix[$campaignId]['events'][] = $event;
    }

    private function fillNonConversionCosts(array &$matrix): void
    {
        foreach ($matrix as $campaignId => $item) {
            $this->computeViewCost($campaignId, $item);

            foreach ($item['events'] as $event) {
                if ($event['type'] !== EventType::CONVERSION) {
                    $count = $matrix[$campaignId][$event['type']][$event['user_id']];
                    $cost = $this->getEventCost($event) / $count;
                    $matrix[$campaignId]['costs_' . $event['type']] += $cost;
                    $matrix[$campaignId]['costs'] += $cost;
                }
            }
        }
    }

    private static function createPayment(string $eventType, string $eventId, int $status, ?int $value = null): array
    {
        return [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'status' => $status,
            'value' => $value,
        ];
    }

    private function getBidStrategyRank(Campaign $campaign, array $event): float
    {
        $bidStrategyForCampaign = $this->getBidStrategyForCampaign($campaign);
        $keywords = $event['keywords'];

        $bidStrategyRank = 1.0;
        foreach ($bidStrategyForCampaign as $category => $valueToRankMap) {
            if (!isset($keywords[$category])) {
                if (isset($valueToRankMap[''])) {
                    $bidStrategyRank *= $valueToRankMap[''];
                }
                continue;
            }

            foreach ($keywords[$category] as $value) {
                if (isset($valueToRankMap[$value])) {
                    $bidStrategyRank *= $valueToRankMap[$value];
                    continue 2;
                }
            }
            if (isset($valueToRankMap['*'])) {
                $bidStrategyRank *= $valueToRankMap['*'];
            }
        }

        return $bidStrategyRank;
    }

    private function getBidStrategyForCampaign(Campaign $campaign): array
    {
        $campaignId = $campaign->getId()->toString();
        if (isset($this->bidStrategiesForCampaigns[$campaignId])) {
            return $this->bidStrategiesForCampaigns[$campaignId];
        }

        $bidStrategyForCampaign = [];

        $bidStrategyId = $campaign->getBidStrategyId()->toString();
        if (isset($this->bidStrategies[$bidStrategyId])) {
            $bidStrategy = $this->bidStrategies[$bidStrategyId];
            $requiredFilters = $campaign->getRequireFilters();
            foreach ($bidStrategy as $category => $valueToRankMap) {
                if (!isset($requiredFilters[$category])) {
                    $bidStrategyForCampaign[$category] = $valueToRankMap;

                    continue;
                }

                $valuesIntersection = array_intersect($requiredFilters[$category], array_keys($valueToRankMap));
                if (isset($valueToRankMap[''])) {
                    $valuesIntersection[] = '';
                }
                if (isset($valueToRankMap['*'])) {
                    $valuesIntersection[] = '*';
                }

                if (empty($valuesIntersection)) {
                    continue;
                }

                $maxRank = 0;
                foreach ($valuesIntersection as $value) {
                    $maxRank = max($maxRank, $valueToRankMap[$value]);
                }

                if (0 === $maxRank) {
                    continue;
                }

                $rankNormalizingFactor = 1 / $maxRank;
                $valueToRankNormalizedMap = [];
                foreach ($valuesIntersection as $value) {
                    $valueToRankNormalizedMap[$value] = $valueToRankMap[$value] * $rankNormalizingFactor;
                }
                $bidStrategyForCampaign[$category] = $valueToRankNormalizedMap;
            }
        }

        $this->bidStrategiesForCampaigns[$campaignId] = $bidStrategyForCampaign;

        return $bidStrategyForCampaign;
    }

    private function computeViewCost(string $campaignId, $item): void
    {
        /** @var Campaign $campaign */
        $campaign = $this->campaigns[$campaignId];
        $this->campaignCosts[$campaignId] = new CampaignCost($this->reportId, $campaign->getId());
        $maxCpm = $campaign->getMaxCpm();
        $cpmFactor = 1.0;
        if ($maxCpm === null) {
            if (
                ($campaignCost = $this->campaignCostRepository->fetch($this->reportId, $campaign->getId())) !== null
            ) {
                $uniqueViews = count($item[EventType::VIEW]);
                $score = $campaignCost->getViewsCost() !== 0 ? $uniqueViews ** 2 / $campaignCost->getViewsCost() : 0;
                $previousScore = $campaignCost->getScore();
                $this->campaignCosts[$campaignId]->setScore($score);

                if (
                    $previousScore === null
                    || $campaign->getBudgetValue() * $this->config->getAutoCpmBudgetThreshold()
                    > $campaignCost->getViewsCost()
                ) {
                    $cpmFactor = self::CPM_INCREASING_FACTOR;
                } else {
                    if ($campaignCost->getCpmFactor() < 1.0 && $uniqueViews >= $campaignCost->getViews()
                        || $campaignCost->getCpmFactor() > 1.0 && $uniqueViews <= $campaignCost->getViews()
                    ) {
                        $cpmFactor = 1.0;
                    } else {
                        $wasCpmDecreasedEarlier = $campaignCost->getCpmFactor() < 1.0;
                        if ($score > $previousScore) {
                            $cpmFactor
                                = $wasCpmDecreasedEarlier ? self::CPM_DECREASING_FACTOR : self::CPM_INCREASING_FACTOR;
                        } else {
                            $cpmFactor
                                = $wasCpmDecreasedEarlier ? self::CPM_INCREASING_FACTOR : self::CPM_DECREASING_FACTOR;
                        }
                    }
                }

                if ($campaignCost->getViewsCost() > 0 && $campaignCost->getViews() > 0) {
                    $maxCpm = (int)((1000 * $campaignCost->getViewsCost() / $campaignCost->getViews()) * $cpmFactor);
                } else {
                    $maxCpm = (int)($campaignCost->getMaxCpm() * $cpmFactor);
                }
            } else {
                $maxCpm = $this->config->getAutoCpmDefault();
            }
        }
        $this->campaignCosts[$campaignId]->setMaxCpm($maxCpm);
        $this->campaignCosts[$campaignId]->setCpmFactor($cpmFactor);
        $this->viewCostByCampaignId[$campaignId] = (int)($maxCpm / 1000);
    }

    private function storeCampaignCosts(): void
    {
        if (!$this->campaignCosts) {
            return;
        }

        $collection = new CampaignCostCollection();
        foreach ($this->campaignCosts as $campaignCost) {
            $collection->add($campaignCost);
        }

        $this->campaignCostRepository->saveAll($collection);
    }
}
