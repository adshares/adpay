<?php

declare(strict_types=1);

namespace App\Tests\Domain\Service;

use App\Domain\Model\Banner;
use App\Domain\Model\BannerCollection;
use App\Domain\Model\BidStrategy;
use App\Domain\Model\BidStrategyCollection;
use App\Domain\Model\Campaign;
use App\Domain\Model\CampaignCollection;
use App\Domain\Model\CampaignCost;
use App\Domain\Model\CampaignCostCollection;
use App\Domain\Model\Conversion;
use App\Domain\Model\ConversionCollection;
use App\Domain\Repository\CampaignCostRepository;
use App\Domain\Service\PaymentCalculator;
use App\Domain\ValueObject\BannerType;
use App\Domain\ValueObject\Budget;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\LimitType;
use App\Domain\ValueObject\PaymentCalculatorConfig;
use App\Domain\ValueObject\PaymentStatus;
use App\Lib\DateTimeHelper;
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

    private const BANNER_SIZE = '100x200';

    private const USER_ID = 'a0000000000000000000000000000001';

    private const CONVERSION_GROUP_ID = 'b0000000000000000000000000000001';

    private const CONVERSION_ID = 'c0000000000000000000000000000001';

    private const CONVERSION_VALUE = 200;

    private const BID_STRATEGY_ID = 'd0000000000000000000000000000001';

    public function testPaymentList(): void
    {
        $reportId = 0;
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection();
        $payments = (new PaymentCalculator(
            $campaigns,
            $bidStrategies,
            $this->getMockedCampaignCostRepository(),
            new PaymentCalculatorConfig()
        ))->calculate($reportId, [self::viewEvent(), self::clickEvent()]);

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
        $payment = $this->single(
            $campaigns,
            self::conversionEvent(['human_score' => 0.5]),
            ['conversionHumanScoreThreshold' => 0.55]
        );
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.3]), ['humanScoreThreshold' => '0.5']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
        $payment =
            $this->single($campaigns, self::conversionEvent(['human_score' => 0.3]), ['humanScoreThreshold' => '0.5']);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);

        $payment = $this->single($campaigns, self::viewEvent(['human_score' => 0.49]), ['humanScoreThreshold' => null]);
        $this->assertEquals(PaymentStatus::HUMAN_SCORE_TOO_LOW, $payment['status']);
        $payment =
            $this->single($campaigns, self::conversionEvent(['human_score' => 0.39]), ['humanScoreThreshold' => '0.5']);
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

    public function testViewEventsOfOneUserDifferentPageRanks(): void
    {
        $campaigns = new CampaignCollection(
            self::campaign(
                [],
                [self::banner()],
            ),
        );

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 50,
                '10000000000000000000000000000011' => 33,
                '10000000000000000000000000000021' => 16,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(['page_rank' => 0.3]),
                    self::viewEvent([
                        'id' => '10000000000000000000000000000011',
                        'page_rank' => 0.2,
                    ]),
                    self::viewEvent([
                        'id' => '10000000000000000000000000000021',
                        'page_rank' => 0.1,
                    ]),
                ],
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

        // one event
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(['page_rank' => 0.4]),
                ]
            )
        );

        // two users
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV * 0.8,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPV * 1.2,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(['page_rank' => 0.4]),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000002',
                            'user_id' => 'a0000000000000000000000000000002',
                            'page_rank' => 0.6
                        ]
                    )
                ]
            )
        );

        // same user
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV * 0.4,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPV * 0.6,
            ],
            $this->values(
                $campaigns,
                [
                    self::viewEvent(['page_rank' => 0.4]),
                    self::viewEvent(['id' => '10000000000000000000000000000002', 'page_rank' => 0.6])
                ]
            )
        );

        // click event
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC / 2,
            ],
            $this->values($campaigns, [self::viewEvent(), self::clickEvent(['page_rank' => 0.5])])
        );

        // conversion
        $this->assertEquals(
            ['10000000000000000000000000000003' => self::CONVERSION_VALUE],
            $this->values($campaigns, [self::conversionEvent(['page_rank' => 0.5])])
        );
    }

    public function testPageRankOutOfRange(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 0,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPC,
            ],
            $this->values($campaigns, [self::viewEvent(['page_rank' => 0]), self::clickEvent(['page_rank' => 2])])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
                '10000000000000000000000000000002' => 0,
            ],
            $this->values($campaigns, [self::viewEvent(), self::clickEvent(['page_rank' => 0])])
        );
        $this->assertEquals(
            ['10000000000000000000000000000003' => self::CONVERSION_VALUE],
            $this->values($campaigns, [self::conversionEvent(['page_rank' => 0])])
        );
    }

    public function testPageRankCpaOnly(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => 0,
            ],
            $this->values($campaigns, [self::viewEvent(['page_rank' => -1])])
        );
        $this->assertEquals(
            [
                '10000000000000000000000000000002' => 0,
            ],
            $this->values($campaigns, [self::clickEvent(['page_rank' => -1])])
        );
        $this->assertEquals(
            ['10000000000000000000000000000003' => self::CONVERSION_VALUE],
            $this->values($campaigns, [self::conversionEvent(['page_rank' => -1])])
        );
    }

    public function testBidStrategy(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:e1_v3', 0.4),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:e1_v4', 0.6)
        );


        // one event
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV,
            ],
            $this->valuesWithCustomBidStrategy($campaigns, $bidStrategies, [self::viewEvent()])
        );

        // two users
        $cpmScale = 2.0; // 1 / ((0.4 + 0.6) / 2)
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV * $cpmScale * 0.4,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPV * $cpmScale * 0.6,
            ],
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies,
                [
                    self::viewEvent(),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000002',
                            'user_id' => 'a0000000000000000000000000000002',
                            'keywords' => ['r1' => ['r1_v1'], 'e1' => ['e1_v4']],
                        ]
                    )
                ]
            )
        );

        // same user
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV * $cpmScale * 0.4 / 2,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPV * $cpmScale * 0.6 / 2,
            ],
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies,
                [
                    self::viewEvent(),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000002',
                            'keywords' => ['r1' => ['r1_v1'], 'e1' => ['e1_v4']],
                        ]
                    )
                ]
            )
        );
    }

    public function testBidStrategies(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v1', 0.8),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v2', 1),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:e1_v3', 0.5),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'c1:c1_v1', 0.6)
        );

        $cpmScale = 1.5; // 1 / ((0.4 + 1 + 0.6) / 3)
        $this->assertEquals(
            [
                '10000000000000000000000000000001' => self::CAMPAIGN_CPV * $cpmScale * 0.8 * 0.5,
                '10000000000000000000000000000002' => self::CAMPAIGN_CPV * $cpmScale * 1.0,
                '10000000000000000000000000000003' => self::CAMPAIGN_CPV * $cpmScale * 1.0 * 0.6,
            ],
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies,
                [
                    self::viewEvent(),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000002',
                            'user_id' => 'a0000000000000000000000000000002',
                            'keywords' => ['r1' => ['r1_v2']],
                        ]
                    ),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000003',
                            'user_id' => 'a0000000000000000000000000000003',
                            'keywords' => ['r1' => ['r1_v2'], 'c1' => ['c1_v1']],
                        ]
                    ),
                ]
            )
        );
    }

    public function testBidStrategyNotMatchingCampaignFilters(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v3', 0.6)
        );
        $events = [self::viewEvent()];

        $result = self::valuesWithCustomBidStrategy($campaigns, $bidStrategies, $events);

        $this->assertEquals(self::CAMPAIGN_CPV, $result['10000000000000000000000000000001']);
    }

    public function testBidStrategyMatchingCampaignFiltersZero(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v1', 0)
        );
        $events = [self::viewEvent()];

        $result = self::valuesWithCustomBidStrategy($campaigns, $bidStrategies, $events);

        $this->assertEquals(self::CAMPAIGN_CPV, $result['10000000000000000000000000000001']);
    }

    public function testBidStrategiesDefaultValue(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies1 = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v1', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v4', 1)
        );
        $bidStrategies2 = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:*', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v4', 1)
        );

        $events = [
            self::viewEvent(
                [
                    'keywords' => ['r1' => ['r1_v4'], 'e1' => []],
                ]
            ),
            self::viewEvent(
                [
                    'id' => '10000000000000000000000000000002',
                    'case_id' => '20000000000000000000000000000002',
                    'impression_id' => '80000000000000000000000000000002',
                    'tracking_id' => '90000000000000000000000000000002',
                    'user_id' => 'a0000000000000000000000000000002',
                    'keywords' => ['r1' => ['r1_v1'], 'e1' => []],
                ]
            ),
        ];

        $this->assertEquals(
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies1,
                $events
            ),
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies2,
                $events
            )
        );
    }

    public function testBidStrategiesMissingValue(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies1 = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v1', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v4', 1)
        );
        $bidStrategies2 = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'z1:', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v4', 1)
        );

        $events = [
            self::viewEvent(
                [
                    'keywords' => ['r1' => ['r1_v4'], 'z1' => ['asd']],
                ]
            ),
            self::viewEvent(
                [
                    'id' => '10000000000000000000000000000002',
                    'case_id' => '20000000000000000000000000000002',
                    'impression_id' => '80000000000000000000000000000002',
                    'tracking_id' => '90000000000000000000000000000002',
                    'user_id' => 'a0000000000000000000000000000002',
                    'keywords' => ['r1' => ['r1_v1'], 'e1' => []],
                ]
            ),
        ];

        $this->assertEquals(
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies2,
                $events
            ),
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies1,
                $events
            )
        );
    }

    public function testBidStrategiesWithNormalization(): void
    {
        $campaigns = new CampaignCollection(self::campaign([], [self::banner()], [self::conversion()]));
        $bidStrategies = new BidStrategyCollection(
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:*', 0),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:e1_v3', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'e1:e1_v4', 2),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v4', 10),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v1', 6),
            new BidStrategy(new Id(self::BID_STRATEGY_ID), 'r1:r1_v2', 2)
        );

        $cpmScale = self::CAMPAIGN_CPV / ((100 + 40) / 2);

        $this->assertEquals(
            [
                '10000000000000000000000000000001' => floor(self::CAMPAIGN_CPV * $cpmScale * 1),
                '10000000000000000000000000000002' => floor(self::CAMPAIGN_CPV * $cpmScale * 0.6 / 2),
                '10000000000000000000000000000003' => floor(self::CAMPAIGN_CPV * $cpmScale * 0.2 / 2),
            ],
            $this->valuesWithCustomBidStrategy(
                $campaigns,
                $bidStrategies,
                [
                    self::viewEvent(
                        [
                            'keywords' => ['r1' => ['r1_v4'], 'e1' => ['e1_v4'], 'z1' => ['x']],
                        ]
                    ),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000002',
                            'case_id' => '20000000000000000000000000000002',
                            'impression_id' => '80000000000000000000000000000002',
                            'tracking_id' => '90000000000000000000000000000002',
                            'user_id' => 'a0000000000000000000000000000002',
                            'keywords' => ['r1' => ['r1_v1'], 'e1' => ['e1_v3']],
                        ]
                    ),
                    self::viewEvent(
                        [
                            'id' => '10000000000000000000000000000003',
                            'case_id' => '20000000000000000000000000000003',
                            'impression_id' => '80000000000000000000000000000003',
                            'tracking_id' => '90000000000000000000000000000002',
                            'user_id' => 'a0000000000000000000000000000002',
                            'keywords' => ['r1' => ['r1_v2'], 'e1' => ['e1_v3']],
                        ]
                    ),
                ]
            )
        );
    }

    public function testAutoCpmEmptyHistory(): void
    {
        $reportId = 0;
        $config = new PaymentCalculatorConfig();
        $campaigns = new CampaignCollection(
            self::campaign(['max_cpm' => null, 'max_cpc' => null], [self::banner()], [self::conversion()])
        );
        $bidStrategies = new BidStrategyCollection();
        $repository = $this->createMock(CampaignCostRepository::class);
        $repository
            ->expects($this->once())
            ->method('fetch')
            ->with($reportId, new Id(self::CAMPAIGN_ID))
            ->willReturn(null);
        $repository
            ->expects($this->once())
            ->method('saveAll')
            ->willReturnCallback(function ($campaignCostCollection) use ($reportId, $config) {
                $this->assertTrue($campaignCostCollection instanceof CampaignCostCollection);
                $this->assertCount(1, $campaignCostCollection);

                /** @var CampaignCost $campaignCost */
                $campaignCost = $campaignCostCollection->first();
                $this->assertEquals($reportId, $campaignCost->getReportId());
                $this->assertEquals(self::CAMPAIGN_ID, $campaignCost->getCampaignId()->toString());
                $this->assertNull($campaignCost->getScore());
                $this->assertEquals($config->getAutoCpmDefault(), $campaignCost->getMaxCpm());
                $this->assertEquals(1.0, $campaignCost->getCpmFactor());
                $this->assertEquals(1, $campaignCost->getViews());
                $this->assertEquals((int)($config->getAutoCpmDefault() / 1000), $campaignCost->getViewsCost());
                $this->assertEquals(0, $campaignCost->getClicks());
                $this->assertEquals(0, $campaignCost->getClicksCost());
                $this->assertEquals(0, $campaignCost->getConversions());
                $this->assertEquals(0, $campaignCost->getConversionsCost());

                return 1;
            });

        $payments = (new PaymentCalculator($campaigns, $bidStrategies, $repository, $config))
            ->calculate($reportId, [self::viewEvent()]);
        $this->assertCount(1, $payments);
    }

    public function testAutoCpmNoScore(): void
    {
        $reportId = 7200;
        $config = new PaymentCalculatorConfig();
        $campaigns = new CampaignCollection(
            self::campaign(['max_cpm' => null, 'max_cpc' => null], [self::banner()], [self::conversion()])
        );
        $bidStrategies = new BidStrategyCollection();
        $repository = $this->createMock(CampaignCostRepository::class);
        $repository
            ->expects($this->once())
            ->method('fetch')
            ->with($reportId, new Id(self::CAMPAIGN_ID))
            ->willReturn(
                new CampaignCost(
                    $reportId - 3600,
                    new Id(self::CAMPAIGN_ID),
                    null,
                    $config->getAutoCpmDefault(),
                    1.0,
                    1000,
                    $config->getAutoCpmDefault(),
                    0,
                    0,
                    0,
                    0
                )
            );
        $repository
            ->expects($this->once())
            ->method('saveAll')
            ->willReturnCallback(function ($campaignCostCollection) use ($reportId, $config) {
                $this->assertTrue($campaignCostCollection instanceof CampaignCostCollection);
                $this->assertCount(1, $campaignCostCollection);

                /** @var CampaignCost $campaignCost */
                $campaignCost = $campaignCostCollection->first();
                $this->assertEquals($reportId, $campaignCost->getReportId());
                $this->assertEquals(self::CAMPAIGN_ID, $campaignCost->getCampaignId()->toString());
                $this->assertNotNull($campaignCost->getScore());
                $this->assertGreaterThan($config->getAutoCpmDefault(), $campaignCost->getMaxCpm());
                $this->assertGreaterThan(1.0, $campaignCost->getCpmFactor());
                $this->assertEquals(500, $campaignCost->getViews());
                $this->assertEquals(self::CAMPAIGN_BUDGET, $campaignCost->getViewsCost());
                $this->assertEquals(0, $campaignCost->getClicks());
                $this->assertEquals(0, $campaignCost->getClicksCost());
                $this->assertEquals(0, $campaignCost->getConversions());
                $this->assertEquals(0, $campaignCost->getConversionsCost());

                return 1;
            });

        $payments = (new PaymentCalculator($campaigns, $bidStrategies, $repository, $config))
            ->calculate($reportId, self::uniqueViewEvents(500));
        $count = 0;
        $cost = 0;
        foreach ($payments as $payment) {
            ++$count;
            $cost += $payment['value'];
        }
        $this->assertEquals(500, $count);
        $this->assertEquals(self::CAMPAIGN_BUDGET, $cost);
    }

    public function testAutoCpmNoViews(): void
    {
        $reportId = 7200;
        $config = new PaymentCalculatorConfig();
        $campaigns = new CampaignCollection(
            self::campaign(['max_cpm' => null, 'max_cpc' => null], [self::banner()], [self::conversion()])
        );
        $bidStrategies = new BidStrategyCollection();
        $repository = $this->createMock(CampaignCostRepository::class);
        $repository
            ->expects($this->once())
            ->method('fetch')
            ->with($reportId, new Id(self::CAMPAIGN_ID))
            ->willReturn(
                new CampaignCost(
                    $reportId - 3600,
                    new Id(self::CAMPAIGN_ID),
                    0,
                    $config->getAutoCpmDefault(),
                    1.0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                )
            );
        $repository
            ->expects($this->once())
            ->method('saveAll')
            ->willReturnCallback(function ($campaignCostCollection) use ($reportId, $config) {
                $this->assertTrue($campaignCostCollection instanceof CampaignCostCollection);
                $this->assertCount(1, $campaignCostCollection);

                /** @var CampaignCost $campaignCost */
                $campaignCost = $campaignCostCollection->first();
                $this->assertEquals($reportId, $campaignCost->getReportId());
                $this->assertEquals(self::CAMPAIGN_ID, $campaignCost->getCampaignId()->toString());
                $this->assertEquals(0.0, $campaignCost->getScore());
                $this->assertGreaterThan($config->getAutoCpmDefault(), $campaignCost->getMaxCpm());
                $this->assertGreaterThan(1.0, $campaignCost->getCpmFactor());
                $this->assertEquals(500, $campaignCost->getViews());
                $this->assertEquals(self::CAMPAIGN_BUDGET, $campaignCost->getViewsCost());
                $this->assertEquals(0, $campaignCost->getClicks());
                $this->assertEquals(0, $campaignCost->getClicksCost());
                $this->assertEquals(0, $campaignCost->getConversions());
                $this->assertEquals(0, $campaignCost->getConversionsCost());

                return 1;
            });

        $payments = (new PaymentCalculator($campaigns, $bidStrategies, $repository, $config))
            ->calculate($reportId, self::uniqueViewEvents(500));
        $count = 0;
        $cost = 0;
        foreach ($payments as $payment) {
            ++$count;
            $cost += $payment['value'];
        }
        $this->assertEquals(500, $count);
        $this->assertEquals(self::CAMPAIGN_BUDGET, $cost);
    }

    public function testAutoCpmNoViewIncreaseOnGreaterCpm(): void
    {
        $reportId = 7200;
        $config = new PaymentCalculatorConfig();
        $campaigns = new CampaignCollection(
            self::campaign(
                ['budget' => 190 * 10 ** 11, 'max_cpm' => null, 'max_cpc' => null],
                [self::banner()],
                [self::conversion()],
            )
        );
        $bidStrategies = new BidStrategyCollection();

        $repository = $this->createMock(CampaignCostRepository::class);
        $previousViews = 5100;
        $previousScore = $previousViews ** 2 / (4900 * $config->getAutoCpmDefault() / 1000);
        $previousMaxCpm = (int)(1.1 * $config->getAutoCpmDefault());
        $previousViewsCost = (int)($previousViews * 1.1 * $config->getAutoCpmDefault() / 1000);

        $views = $previousViews;

        $previousCampaignCost = new CampaignCost(
            $reportId - 3600,
            new Id(self::CAMPAIGN_ID),
            $previousScore,
            $previousMaxCpm,
            1.1,
            $previousViews,
            $previousViewsCost,
            0,
            0,
            0,
            0
        );
        $repository
            ->expects($this->once())
            ->method('fetch')
            ->with($reportId, new Id(self::CAMPAIGN_ID))
            ->willReturn($previousCampaignCost);
        $repository
            ->expects($this->once())
            ->method('saveAll')
            ->willReturnCallback(function ($campaignCostCollection) use ($reportId, $config, $previousCampaignCost) {
                $this->assertTrue($campaignCostCollection instanceof CampaignCostCollection);
                $this->assertCount(1, $campaignCostCollection);

                /** @var CampaignCost $campaignCost */
                $campaignCost = $campaignCostCollection->first();
                $this->assertEquals($reportId, $campaignCost->getReportId());
                $this->assertEquals(self::CAMPAIGN_ID, $campaignCost->getCampaignId()->toString());
                $this->assertLessThan($previousCampaignCost->getScore(), $campaignCost->getScore());
                $this->assertGreaterThan($previousCampaignCost->getMaxCpm(), $campaignCost->getMaxCpm());
                $this->assertGreaterThan(1.0, $campaignCost->getCpmFactor());
                $this->assertEquals($previousCampaignCost->getViews(), $campaignCost->getViews());
                $this->assertGreaterThan($previousCampaignCost->getViewsCost(), $campaignCost->getViewsCost());
                $this->assertEquals(0, $campaignCost->getClicks());
                $this->assertEquals(0, $campaignCost->getClicksCost());
                $this->assertEquals(0, $campaignCost->getConversions());
                $this->assertEquals(0, $campaignCost->getConversionsCost());

                return 1;
            });

        $payments = (new PaymentCalculator($campaigns, $bidStrategies, $repository, $config))
            ->calculate($reportId, self::uniqueViewEvents($views));
        $this->assertCount($views, $payments);
    }

    /**
     * This function can be used to check how auto cpm works in long term.
     */
    private function testAutoCpm(): void
    {
        function viewsByCpm($cpm): int
        {
            return (int)(10000 / (1 + M_E ** (8 - 0.01 * $cpm / 10 ** 9)));
        }

//        for ($i = 0; $i < 100; $i++) {
//            $t[] = (5 + $i / 2) * 10 ** 11;
//        }
//        foreach ($t as $cpm) {
//            $viewsByCpm = viewsByCpm($cpm);
//            $cost = $viewsByCpm * $cpm / 1000;
//        }

        $config = new PaymentCalculatorConfig();
        $campaigns = new CampaignCollection(
            self::campaign(
                ['budget' => 140 * 10 ** 11, 'max_cpm' => null, 'max_cpc' => null,],
                [self::banner()],
                [self::conversion()],
            )
        );
        $bidStrategies = new BidStrategyCollection();
        /** @var CampaignCost $campaignCost */
        $campaignCost = null;
        $previousCpm = 0;
        $views = 0;
        for ($loop = 0; $loop < 50; $loop++) {
            $reportId = ($loop + 1) * 3600;

            $repository = $this->createMock(CampaignCostRepository::class);
            $repository
                ->expects($this->once())
                ->method('fetch')
                ->willReturn($campaignCost);
            $repository
                ->expects($this->once())
                ->method('saveAll')
                ->willReturnCallback(function ($campaignCostCollection) use (&$campaignCost) {
                    $this->assertCount(1, $campaignCostCollection);
                    $campaignCost = $campaignCostCollection[0];
                    return 1;
                });

            $cpm = $campaignCost != null
                ? (int)(1000 * $campaignCost->getViewsCost() / $campaignCost->getViews())
                : $config->getAutoCpmDefault();

            if ($loop > 0) {
                echo sprintf(
                    "%3d\t%6.3f\t%+.3f\t%5d\t%7.3f\n",
                    $loop,
                    $cpm / 10 ** 11,
                    ($cpm - $previousCpm) / 10 ** 11,
                    $views,
                    $campaignCost->getViewsCost() / 10 ** 11
                );
            }
            $views = viewsByCpm($cpm);
            $previousCpm = $cpm;

            $payments = (new PaymentCalculator($campaigns, $bidStrategies, $repository, $config))
                ->calculate($reportId, self::uniqueViewEvents($views));
            $this->assertCount($views, $payments);
        }
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
        $reportId = 0;
        $bidStrategies = new BidStrategyCollection();
        $payments = (new PaymentCalculator(
            $campaigns,
            $bidStrategies,
            $this->getMockedCampaignCostRepository(),
            new PaymentCalculatorConfig($config)
        ))->calculate($reportId, [$event]);
        $result = [];

        foreach ($payments as $payment) {
            if (
                $payment['event_type'] === $event['type']
                && $payment['event_id'] === $event['id']
            ) {
                $result = $payment;
            }
        }

        return $result;
    }

    private function values(CampaignCollection $campaigns, array $events, array $config = []): array
    {
        return $this->valuesWithCustomBidStrategy($campaigns, new BidStrategyCollection(), $events, $config);
    }

    private function valuesWithCustomBidStrategy(
        CampaignCollection $campaigns,
        BidStrategyCollection $bidStrategies,
        array $events,
        array $config = []
    ): array {
        $reportId = 0;
        $payments = (new PaymentCalculator(
            $campaigns,
            $bidStrategies,
            $this->getMockedCampaignCostRepository(),
            new PaymentCalculatorConfig($config)
        ))->calculate($reportId, $events);
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
        $filters = ['require' => ['r1' => ['r1_v1', 'r1_v2', 'r1_v4']], 'exclude' => ['e1' => ['e1_v1', 'e1_v2']]];

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
                'bid_strategy_id' => self::BID_STRATEGY_ID,
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
            new Id($data['bid_strategy_id']),
            $data['deleted_at'] !== null ? DateTimeHelper::fromTimestamp($data['deleted_at']) : null
        );
    }

    private static function banner(array $mergeData = []): Banner
    {
        $data = array_merge(
            [
                'id' => self::BANNER_ID,
                'campaign_id' => self::CAMPAIGN_ID,
                'size' => self::BANNER_SIZE,
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

    private static function uniqueViewEvents($count): array
    {
        $events = [];
        for ($i = 0; $i < $count; $i++) {
            $id = str_pad((string)$i, 31, '0', STR_PAD_LEFT);
            $events[] = self::viewEvent([
                'id' => '1' . $id,
                'user_id' => 'a' . $id,
            ]);
        }
        return $events;
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

    private function getMockedCampaignCostRepository(): CampaignCostRepository
    {
        $repository = $this->createMock(CampaignCostRepository::class);
        $repository->expects($this->never())->method('fetch');

        return $repository;
    }
}
