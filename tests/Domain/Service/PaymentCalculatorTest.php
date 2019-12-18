<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Banner;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
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
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class PaymentCalculatorTest extends TestCase
{
    private const TIME = 1571231623;

    private const ADVERTISER_ID = '50000000000000000000000000000001';

    private const CAMPAIGN_ID = '60000000000000000000000000000001';

    private const CAMPAIGN_BUDGET = 10000000000;

    private const CAMPAIGN_CPV = 100;

    private const CAMPAIGN_CPC = 1500000;

    private const BANNER_ID = '70000000000000000000000000000001';

    private const BANNER_SZIE = '100x200';

    private const USER_ID = 'a0000000000000000000000000000001';

    private const CONVERSION_GROUP_ID = 'b0000000000000000000000000000001';

    private const CONVERSION_ID = 'c0000000000000000000000000000001';

    private const CONVERSION_VALUE = 200;

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
        $this->statusForAll(PaymentStatus::ACCEPTED, [], ['deleted_at' => self::TIME + 10]);
        $this->statusForAll(PaymentStatus::ACCEPTED, [], ['deleted_at' => self::TIME - 10]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_NOT_FOUND, [], ['deleted_at' => self::TIME - 110]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_NOT_FOUND, [], ['deleted_at' => self::TIME - 3600 * 24]);
    }

    public function testCampaignOutdated(): void
    {
        $this->statusForAll(PaymentStatus::ACCEPTED, [], ['time_end' => self::TIME + 10]);
        $this->statusForAll(PaymentStatus::ACCEPTED, [], ['time_end' => self::TIME - 10]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_end' => self::TIME - 110]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_end' => self::TIME - 3600 * 24]);

        $this->statusForAll(PaymentStatus::ACCEPTED, [], ['time_start' => self::TIME - 110]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_start' => self::TIME - 10]);
        $this->statusForAll(PaymentStatus::CAMPAIGN_OUTDATED, [], ['time_start' => self::TIME + 3600 * 24]);
    }

    public function testBannerNotExist(): void
    {
        $this->statusForAll(PaymentStatus::BANNER_NOT_FOUND, ['banner_id' => '7000000000000000000000000000000f']);
    }

    public function testBannerDeleted(): void
    {
        $this->statusForAll(PaymentStatus::ACCEPTED, [], [], ['deleted_at' => self::TIME + 10]);
        $this->statusForAll(PaymentStatus::ACCEPTED, [], [], ['deleted_at' => self::TIME - 10]);
        $this->statusForAll(PaymentStatus::BANNER_NOT_FOUND, [], [], ['deleted_at' => self::TIME - 110]);
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
        $this->assertEquals(PaymentStatus::CONVERSION_NOT_FOUND, $payment['status']);
    }

    public function testConversionDeleted(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign([], [self::banner()], [self::conversion(['deleted_at' => self::TIME + 10])])
        );
        $payment = $this->single($campaigns, self::conversionEvent());
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);


        $campaigns = new CampaignCollection(
            self::campaign([], [self::banner()], [self::conversion(['deleted_at' => self::TIME - 10])])
        );
        $payment = $this->single($campaigns, self::conversionEvent());
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);

        $campaigns = new CampaignCollection(
            self::campaign([], [self::banner()], [self::conversion(['deleted_at' => self::TIME - 110])])
        );
        $payment = $this->single($campaigns, self::conversionEvent());
        $this->assertEquals(PaymentStatus::CONVERSION_NOT_FOUND, $payment['status']);

        $campaigns = new CampaignCollection(
            self::campaign([], [self::banner()], [self::conversion(['deleted_at' => self::TIME - 3600 * 24])])
        );
        $payment = $this->single($campaigns, self::conversionEvent());
        $this->assertEquals(PaymentStatus::CONVERSION_NOT_FOUND, $payment['status']);
    }

    public function testPreviousState(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign([], [self::banner()], [self::conversion()])
        );

        $payment = $this->single($campaigns, self::conversionEvent(['payment_status' => PaymentStatus::ACCEPTED]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);

        $payment =
            $this->single($campaigns, self::conversionEvent(['payment_status' => PaymentStatus::HUMAN_SCORE_TOO_LOW]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);

        $payment =
            $this->single($campaigns, self::conversionEvent(['payment_status' => PaymentStatus::CAMPAIGN_OUTDATED]));
        $this->assertEquals(PaymentStatus::CAMPAIGN_OUTDATED, $payment['status']);

        $payment =
            $this->single($campaigns, self::conversionEvent(['payment_status' => PaymentStatus::INVALID_TARGETING]));
        $this->assertEquals(PaymentStatus::INVALID_TARGETING, $payment['status']);
    }

    public function testHumanScore(): void
    {
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0]);
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0.3]);
        $this->statusForAll(PaymentStatus::HUMAN_SCORE_TOO_LOW, ['human_score' => 0.399]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.5]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.501]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 0.7]);
        $this->statusForAll(PaymentStatus::ACCEPTED, ['human_score' => 1]);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.499]));
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.4]));
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::clickEvent(['human_score' => 0.499]));
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::clickEvent(['human_score' => 0.4]));
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.499]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);

        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.4]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);
    }

    public function testHumanScoreThreshold(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.5]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.4]));
        $this->assertEquals(PaymentStatus::ACCEPTED, $payment['status']);

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.5]), ['humanScoreThreshold' => 0.55]);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.5]), ['conversionHumanScoreThreshold' => 0.55]);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.3]), ['humanScoreThreshold' => '0.5']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.3]), ['humanScoreThreshold' => '0.5']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.49]), ['humanScoreThreshold' => null]);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
        $payment = $this->single($campaigns, self::conversionEvent(['human_score' => 0.39]), ['humanScoreThreshold' => '0.5']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
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
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

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
                '10000000000000000000000000000003' => self::CONVERSION_VALUE,
            ],
            $this->values($campaigns, [self::conversionEvent()])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC,
                '10000000000000000000000000000003' => self::CONVERSION_VALUE,
            ],
            $this->values($campaigns, [self::viewEvent(), self::clickEvent(), self::conversionEvent()])
        );
    }

    public function testMultipleEvents(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign(
                [],
                [self::banner()],
                [
                    self::conversion(),
                    self::conversion(['id' => 'c0000000000000000000000000000002', 'is_repeatable' => true]),
                ]
            ),
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
                '10000000000000000000000000000003' => self::CONVERSION_VALUE,
                '10000000000000000000000000000032' => self::CONVERSION_VALUE,
                '10000000000000000000000000000033' => 0,
                '10000000000000000000000000000034' => 0,
                '10000000000000000000000000000035' => self::CONVERSION_VALUE,
                '10000000000000000000000000000036' => self::CONVERSION_VALUE,
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
                    self::conversionEvent(),
                    self::conversionEvent(['id' => '10000000000000000000000000000032']),
                    self::conversionEvent(
                        ['id' => '10000000000000000000000000000033', 'group_id' => 'b0000000000000000000000000000002']
                    ),
                    self::conversionEvent(
                        ['id' => '10000000000000000000000000000034', 'group_id' => 'b0000000000000000000000000000002']
                    ),
                    self::conversionEvent(
                        [
                            'id' => '10000000000000000000000000000035',
                            'conversion_id' => 'c0000000000000000000000000000002',
                            'group_id' => 'b0000000000000000000000000000003',
                        ]
                    ),
                    self::conversionEvent(
                        [
                            'id' => '10000000000000000000000000000036',
                            'conversion_id' => 'c0000000000000000000000000000002',
                            'group_id' => 'b0000000000000000000000000000004',
                        ]
                    ),
                ]
            )
        );
    }

    public function testOverBudget(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign(
                ['budget' => 500, 'max_cpm' => 300000, 'max_cpc' => 700],
                [self::banner()],
                [
                    self::conversion(),
                    self::conversion(
                        ['id' => 'c0000000000000000000000000000002', 'limit_type' => LimitType::OUT_OF_BUDGET]
                    ),
                ]
            )
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
                        [
                            'id' => '10000000000000000000000000000011',
                            'user_id' => 'a0000000000000000000000000000002',
                        ]
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
                '10000000000000000000000000000003' => 166,
                '10000000000000000000000000000031' => 166,
                '10000000000000000000000000000032' => 166,
                '10000000000000000000000000000033' => self::CONVERSION_VALUE,
                '10000000000000000000000000000034' => self::CONVERSION_VALUE,
            ],
            $this->values(
                $campaigns,
                [
                    self::conversionEvent(),
                    self::conversionEvent(['id' => '10000000000000000000000000000031']),
                    self::conversionEvent(
                        [
                            'id' => '10000000000000000000000000000032',
                            'user_id' => 'a0000000000000000000000000000002',
                        ]
                    ),
                    self::conversionEvent(
                        [
                            'id' => '10000000000000000000000000000033',
                            'conversion_id' => 'c0000000000000000000000000000002',
                        ]
                    ),
                    self::conversionEvent(
                        [
                            'id' => '10000000000000000000000000000034',
                            'conversion_id' => 'c0000000000000000000000000000002',
                        ]
                    ),
                ]
            )
        );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 68,
                '10000000000000000000000000000011' => 68,
                '10000000000000000000000000000002' => 159,
                '10000000000000000000000000000012' => 159,
                '10000000000000000000000000000003' => 45,
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
                    self::conversionEvent(),
                ]
            )
        );

        $this->assertEquals(
            ['10000000000000000000000000000003' => 500],
            $this->values($campaigns, [self::conversionEvent(['conversion_value' => 501])])
        );
    }

    public function testZeroCosts(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign(['max_cpm' => 0, 'max_cpc' => 0], [self::banner()], [self::conversion()])
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

        $this->assertEquals(
            ['10000000000000000000000000000003' => 0],
            $this->values($campaigns, [self::conversionEvent(['conversion_value' => 0])])
        );
    }

    public function testPageRank(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

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
        $this->assertEquals(
            ['10000000000000000000000000000003' => self::CONVERSION_VALUE],
            $this->values($campaigns, [self::conversionEvent(['page_rank' => 0.5])])
        );
    }

    private function statusForAll(
        int $status,
        array $eventData = [],
        array $campaignData = [],
        array $bannerData = [],
        array $conversionData = []
    ) {
        $campaigns = new CampaignCollection(
            self::campaign($campaignData, [self::banner($bannerData)], [self::conversion($conversionData)])
        );

        $payment = $this->single($campaigns, self::viewEvent($eventData));
        $this->assertEquals($status, $payment['status']);

        $payment = $this->single($campaigns, self::clickEvent($eventData));
        $this->assertEquals($status, $payment['status']);

        $payment = $this->single($campaigns, self::conversionEvent($eventData));
        $this->assertEquals($status, $payment['status']);
    }

    private function single(CampaignCollection $campaigns, array $event, array $config = []): array
    {
        $payments = (new PaymentCalculator($campaigns, $config))->calculate([$event]);
        $result = [];

        foreach ($payments as $payment) {
            if ($payment['event_type'] === $event['type']
                && $payment['event_id'] === $event['id']) {
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
            if ($payment['status'] === PaymentStatus::ACCEPTED) {
                $result[$payment['event_id']] = $payment['value'];
            }
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
            $data['size'],
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
                'limit_type' => LimitType::IN_BUDGET,
                'is_repeatable' => false,
                'deleted_at' => null,
            ],
            $mergeData
        );

        return new Conversion(
            new Id($data['id']),
            new Id($data['campaign_id']),
            new LimitType($data['limit_type']),
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
                'group_id' => self::CONVERSION_GROUP_ID,
                'conversion_id' => self::CONVERSION_ID,
                'conversion_value' => self::CONVERSION_VALUE,
            ],
            $mergeData
        );
    }

    private static function event(): array
    {
        return [
            'id' => '10000000000000000000000000000000',
            'time' => DateTimeHelper::fromTimestamp(self::TIME)->format(DateTimeInterface::ATOM),
            'case_id' => '20000000000000000000000000000001',
            'case_time' => DateTimeHelper::fromTimestamp(self::TIME - 100)->format(DateTimeInterface::ATOM),
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
