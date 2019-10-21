<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\Service\PaymentCalculator;
use Adshares\AdPay\Domain\ValueObject\BannerType;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Domain\ValueObject\Size;
use Adshares\AdPay\Lib\DateTimeHelper;
use PHPUnit\Framework\TestCase;

final class PaymentCalculatorTest extends TestCase
{
    private const TIME = 1571231623;

    private const ADVERTISER_ID = '50000000000000000000000000000001';

    private const CAMPAIGN_ID = '60000000000000000000000000000001';

    private const CAMPAIGN_BUDGET = 10000000000;

    private const CAMPAIGN_CPV = 100;

    private const CAMPAIGN_CPC = 1000000;

    private const BANNER_ID = '70000000000000000000000000000001';

    private const BANNER_SZIE = '100x200';

    private const USER_ID = 'a0000000000000000000000000000001';

    private const CONVERSION_GROUP_ID = 'b0000000000000000000000000000001';

    private const CONVERSION_ID = 'c0000000000000000000000000000001';

    public function testPaymentList(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payments = (new PaymentCalculator($campaigns))->calculate([self::viewEvent(), self::clickEvent()]);

        $list = [];
        array_push($list, ...$payments);

        $this->assertCount(2, $list);
    }

    public function testCampaignNotExist(): void
    {
        $this->statusForAll(PaymentStatus::CAMPAIGN_NOT_FOUND, ['campaign_id' => '6000000000000000000000000000000f']);
    }

    public function testCampaignDeleted(): void
    {
        $this->statusForAll(PaymentStatus::CAMPAIGN_NOT_FOUND, [], ['deleted_at' => self::TIME - 3600 * 24]);
    }

    public function testCampaignOutdated(): void
    {
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_end' => self::TIME - 3600 * 24]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_start' => self::TIME + 3600 * 24]);
    }

    public function testBannerNotExist(): void
    {
        $this->statusForAll(PaymentStatus::BANNER_NOT_FOUND, ['banner_id' => '7000000000000000000000000000000f']);
    }

    public function testBannerDeleted(): void
    {
        $this->statusForAll(PaymentStatus::BANNER_NOT_FOUND, [], [], ['deleted_at' => self::TIME - 3600 * 24]);
    }

    public function testConversionNotExist(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

        $payment = $this->single(
            $campaigns,
            self::conversionEvent(
                [
                    'conversion_id' => 'c000000000000000000000000000000f',
                ]
            )
        );
        $this->assertEquals(PaymentStatus::CONVERSION_NOT_FOUND, $payment->getStatusCode());
    }

    public function testConversionDeleted(): void
    {
        $campaigns =
            new CampaignCollection(
                self::campaign([], [self::banner()], [self::conversion(['deleted_at' => self::TIME - 3600 * 24])])
            );

        $payment = $this->single($campaigns, self::conversionEvent());
        $this->assertEquals(PaymentStatus::CONVERSION_NOT_FOUND, $payment->getStatusCode());
    }

    public function testPreviousState(): void
    {
        $this->statusForAll(PaymentStatus::CAMPAIGN_NOT_FOUND, [], ['deleted_at' => self::TIME - 3600 * 24]);
    }

    public function testHumanScore(): void
    {
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0]);
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0.3]);
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0.499]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.5]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.501]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.7]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 1]);
    }

    public function testHumanScoreThreshold(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.5]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment->getStatusCode());

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.5]), ['humanScoreThreshold' => 0.55]);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment->getStatusCode());
    }

    public function testKeywords(): void
    {
        $this->statusForAll(PaymentStatus::ACCEPTED);
        $this->statusForAll(PaymentStatus::INVALID_TARGETING, ['keywords' => ['r1' => ['r1_v3']]]);
        $this->statusForAll(PaymentStatus::INVALID_TARGETING, ['keywords' => ['e1' => ['e1_v1']]]);
        $this->statusForAll(PaymentStatus::INVALID_TARGETING, [], ['filters' => ['require' => ['r1' => ['r1_v3']]]]);
        $this->statusForAll(PaymentStatus::INVALID_TARGETING, [], ['filters' => ['exclude' => ['e1' => ['e1_v3']]]]);
    }

    public function testSimpleEvents(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()]));

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
            ],
            $this->values($campaigns, [self::viewEvent()])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC,
            ],
            $this->values($campaigns, [self::clickEvent()])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC,
            ],
            $this->values($campaigns, [self::viewEvent(), self::clickEvent()])
        );
    }

    public function testMultipleEvents(): void
    {
        $campaigns =
            new CampaignCollection(
                self::campaign([], [self::banner()]),
                self::campaign(
                    ['id' => '60000000000000000000000000000002', 'max_cpm' => 123000],
                    [self::banner(['id' => '70000000000000000000000000000002'])]
                )
            );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 33,
                '10000000000000000000000000000011' => 33,
                '10000000000000000000000000000021' => 33,
                '10000000000000000000000000000101' => 123,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(),
                    self::viewEvent(['id' => '10000000000000000000000000000011']),
                    self::viewEvent(['id' => '10000000000000000000000000000021']),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000101',
                            'campaign_id' => '60000000000000000000000000000002',
                        ]
                    ),
                    self::clickEvent(),
                ]
            )
        );
    }

    public function testOverBudget(): void
    {
        $campaigns =
            new CampaignCollection(
                self::campaign(['budget' => 500, 'max_cpm' => 300000, 'max_cpc' => 700], [self::banner()])
            );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 250,
                '10000000000000000000000000000011' => 250,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(),
                    self::viewEvent(
                        ['id' => '10000000000000000000000000000011', 'user_id' => 'a0000000000000000000000000000002']
                    ),
                ]
            )
        );

        $this->assertEquals(
            ['10000000000000000000000000000002' => 500],
            $this->values($campaigns, [self::clickEvent()])
        );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 75,
                '10000000000000000000000000000011' => 75,
                '10000000000000000000000000000002' => 175,
                '10000000000000000000000000000012' => 175,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(),
                    self::viewEvent(
                        ['id' => '10000000000000000000000000000011', 'user_id' => 'a0000000000000000000000000000002']
                    ),
                    self::clickEvent(),
                    self::clickEvent(
                        ['id' => '10000000000000000000000000000012', 'user_id' => 'a0000000000000000000000000000002']
                    ),
                ]
            )
        );
    }

    public function testZeroCosts(): void
    {
        $campaigns =
            new CampaignCollection(
                self::campaign(['max_cpm' => 0, 'max_cpc' => 0], [self::banner()])
            );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 0,
                '10000000000000000000000000000011' => 0,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(),
                    self::viewEvent(['id' => '10000000000000000000000000000011']),
                ]
            )
        );
    }

    public function testPageRank(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()]));

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV / 4,
            ],
            $this->values($campaigns, [self::viewEvent(['page_rank' => 0.25])])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC / 2,
            ],
            $this->values($campaigns, [self::viewEvent(), self::clickEvent(['page_rank' => 0.5])])
        );
    }

    private function statusForAll(
        int $status,
        array $eventData = [],
        array $campaignData = [],
        array $bannerData = [],
        array $conversionData = []
    ) {
        $campaigns =
            new CampaignCollection(
                self::campaign($campaignData, [self::banner($bannerData)], [self::conversion($conversionData)])
            );

        $payment = $this->single($campaigns, self::viewEvent($eventData));
        $this->assertEquals($status, $payment->getStatusCode());

        $payment = $this->single($campaigns, self::clickEvent($eventData));
        $this->assertEquals($status, $payment->getStatusCode());

        $payment = $this->single($campaigns, self::conversionEvent($eventData));
        $this->assertEquals($status, $payment->getStatusCode());
    }

    private function single(CampaignCollection $campaigns, array $event, array $config = []): ?Payment
    {
        $payments = (new PaymentCalculator($campaigns, $config))->calculate([$event]);
        $result = null;

        foreach ($payments as $payment) {
            /** @var Payment $payment */
            if ($payment->getEventType()->toString() === $event['type']
                && $payment->getEventId()->toString() === $event['id']) {
                $result = $payment;
            }
        }

        return $result;
    }

    private function values(CampaignCollection $campaigns, array $events, array $config = []): array
    {
        $payments = (new PaymentCalculator($campaigns, $config))->calculate($events);
        $result = [];

        foreach ($payments as $payment) {
            /** @var Payment $payment */
            $result[$payment->getEventId()->toString()] = $payment->getValue();
        }

        return $result;
    }

    private static function campaign(array $mergeData = [], array $banners = [], array $conversions = []): Campaign
    {
        $filters = ['require' => ['r1' => ['r1_v1', 'r1_v2']], 'exclude' => ['e1' => ['e1_v1', 'e1_v2']]];

        $data = array_merge(
            [
                'id' => self::CAMPAIGN_ID,
                'advertiser_id' => self::ADVERTISER_ID,
                'time_start' => self::TIME - 7 * 24 * 3600,
                'time_end' => null,
                'filters' => $filters,
                'budget' => self::CAMPAIGN_BUDGET,
                'max_cpm' => self::CAMPAIGN_CPV * 1000,
                'max_cpc' => self::CAMPAIGN_CPC,
                'deleted_at' => null,
            ],
            $mergeData
        );

        $budget = new Budget(
            $data['budget'],
            $data['max_cpm'] !== null ? $data['max_cpm'] : null,
            $data['max_cpc'] !== null ? $data['max_cpc'] : null
        );

        return new Campaign(
            new Id($data['id']),
            new Id($data['advertiser_id']),
            DateTimeHelper::fromTimestamp($data['time_start']),
            $data['time_end'] !== null ? DateTimeHelper::fromTimestamp($data['time_end']) : null,
            $budget,
            new BannerCollection(...$banners),
            $data['filters'],
            new ConversionCollection(...$conversions),
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function banner(array $mergeData = []): Banner
    {
        $data = array_merge(
            [
                'id' => self::BANNER_ID,
                'campaign_id' => self::CAMPAIGN_ID,
                'size' => self::BANNER_SZIE,
                'type' => BannerType::IMAGE,
                'deleted_at' => null,
            ],
            $mergeData
        );

        return new Banner(
            new Id($data['id']),
            new Id($data['campaign_id']),
            Size::fromString($data['size']),
            new BannerType($data['type']),
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function conversion(array $mergeData = []): Conversion
    {
        $data = array_merge(
            [
                'id' => self::CONVERSION_ID,
                'campaign_id' => self::CAMPAIGN_ID,
                'limit' => null,
                'limit_type' => LimitType::IN_BUDGET,
                'cost' => 0,
                'is_value_mutable' => false,
                'is_repeatable' => false,
                'deleted_at' => null,
            ],
            $mergeData
        );

        $limit =
            new Limit(
                $data['limit'] !== null ? $data['limit'] : null,
                new LimitType($data['limit_type']),
                $data['cost']
            );

        return new Conversion(
            new Id($data['id']),
            new Id($data['campaign_id']),
            $limit,
            $data['is_value_mutable'],
            $data['is_repeatable'],
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function viewEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'id' => '10000000000000000000000000000001',
                'type' => EventType::VIEW,
            ],
            $mergeData
        );
    }

    private static function clickEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'id' => '10000000000000000000000000000002',
                'type' => EventType::CLICK,
            ],
            $mergeData
        );
    }

    private static function conversionEvent(array $mergeData = []): array
    {
        return array_merge(
            self::event(),
            [
                'id' => '10000000000000000000000000000003',
                'type' => EventType::CONVERSION,
                'payment_status' => null,
                'conversion_group_id' => self::CONVERSION_GROUP_ID,
                'conversion_id' => self::CONVERSION_ID,
                'conversion_value' => 100,
            ],
            $mergeData
        );
    }

    private static function event(): array
    {
        return [
            'id' => '10000000000000000000000000000000',
            'time' => self::TIME,
            'case_id' => '20000000000000000000000000000001',
            'publisher_id' => '30000000000000000000000000000001',
            'zone_id' => '40000000000000000000000000000001',
            'advertiser_id' => self::ADVERTISER_ID,
            'campaign_id' => self::CAMPAIGN_ID,
            'banner_id' => self::BANNER_ID,
            'impression_id' => '80000000000000000000000000000001',
            'tracking_id' => '90000000000000000000000000000001',
            'user_id' => self::USER_ID,
            'page_rank' => 1.0,
            'human_score' => 0.9,
            'keywords' => ['r1' => ['r1_v1'], 'e1' => ['e1_v3']],
            'context' => [],
        ];
    }
}
