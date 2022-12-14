<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\Model\BannerCollection;
use App\Domain\Model\Campaign;
use App\Domain\Model\ConversionCollection;
use App\Domain\ValueObject\Budget;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\Medium;
use App\Lib\DateTimeHelper;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class CampaignTest extends TestCase
{
    public function testInstanceOfCampaign(): void
    {
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $advertiserId = 'ffc567e1396b4cadb52223a51796fd02';
        $medium = Medium::Web;
        $vendor = null;
        $timeStart = '2019-01-01T12:00:00+00:00';
        $timeEnd = '2019-03-03T09:00:00+00:00';
        $bidStrategyId = '43c567e1396b4cadb52223a51796fd02';
        $deletedAt = '2019-01-01T12:00:00+00:00';

        $budgetValue = 1000000;
        $macCpm = 20;
        $maxCpc = 40;

        $budget = new Budget($budgetValue, $macCpm, $maxCpc);

        $banners = new BannerCollection();
        $filters = ['require' => [1], 'exclude' => [2]];
        $conversions = new ConversionCollection();

        $campaign = new Campaign(
            new Id($campaignId),
            new Id($advertiserId),
            $medium,
            $vendor,
            DateTimeHelper::fromString($timeStart),
            DateTimeHelper::fromString($timeEnd),
            $budget,
            $banners,
            $filters,
            $conversions,
            new Id($bidStrategyId)
        );

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals($campaignId, $campaign->getId());
        $this->assertEquals($advertiserId, $campaign->getAdvertiserId());
        $this->assertEquals($medium, $campaign->getMedium());
        $this->assertEquals($vendor, $campaign->getVendor());
        $this->assertEquals($timeStart, $campaign->getTimeStart()->format(DateTimeInterface::ATOM));
        $this->assertEquals($timeEnd, $campaign->getTimeEnd()->format(DateTimeInterface::ATOM));
        $this->assertEquals($budget, $campaign->getBudget());
        $this->assertEquals($budgetValue, $campaign->getBudgetValue());
        $this->assertEquals($macCpm, $campaign->getMaxCpm());
        $this->assertEquals($maxCpc, $campaign->getMaxCpc());
        $this->assertEquals($banners, $campaign->getBanners());
        $this->assertEquals($filters, $campaign->getFilters());
        $this->assertEquals($conversions, $campaign->getConversions());
        $this->assertEquals($bidStrategyId, $campaign->getBidStrategyId());
        $this->assertNull($campaign->getDeletedAt());
        $this->assertTrue($campaign->isWeb());
        $this->assertFalse($campaign->isMetaverse());

        $mMedium = Medium::Metaverse;
        $mVendor = 'my-metaverse';

        $campaign = new Campaign(
            new Id($campaignId),
            new Id($advertiserId),
            $mMedium,
            $mVendor,
            DateTimeHelper::fromString($timeStart),
            DateTimeHelper::fromString($timeEnd),
            $budget,
            $banners,
            $filters,
            $conversions,
            new Id($bidStrategyId),
            DateTimeHelper::fromString($deletedAt)
        );

        $this->assertEquals($mMedium, $campaign->getMedium());
        $this->assertEquals($mVendor, $campaign->getVendor());
        $this->assertEquals($deletedAt, $campaign->getDeletedAt()->format(DateTimeInterface::ATOM));
        $this->assertFalse($campaign->isWeb());
        $this->assertTrue($campaign->isMetaverse());
    }

    public function testEmptyFilters(): void
    {
        $campaign = new Campaign(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd0f'),
            Medium::Web,
            null,
            new DateTime(),
            new DateTime(),
            new Budget(100),
            new BannerCollection(),
            [],
            new ConversionCollection(),
            new Id('43c567e1396b4cadb52223a51796fd02')
        );

        $this->assertEmpty($campaign->getRequireFilters());
        $this->assertEmpty($campaign->getExcludeFilters());
        $this->assertEquals(['require' => [], 'exclude' => []], $campaign->getFilters());
    }

    public function testInvalidTimeEnd(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Campaign(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            Medium::Web,
            null,
            DateTimeHelper::fromString('2019-01-02T12:00:00+00:00'),
            DateTimeHelper::fromString('2019-01-01T12:00:00+00:00'),
            new Budget(10),
            new BannerCollection(),
            [],
            new ConversionCollection(),
            new Id('43c567e1396b4cadb52223a51796fd02')
        );
    }

    /**
     * @dataProvider filteringDataProvider
     */
    public function testFiltering(array $require, array $exclude, array $keywords, bool $result): void
    {
        $campaign = new Campaign(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            Medium::Web,
            null,
            DateTimeHelper::fromString('2019-01-02T12:00:00+00:00'),
            DateTimeHelper::fromString('2019-01-03T12:00:00+00:00'),
            new Budget(10),
            new BannerCollection(),
            ['require' => $require, 'exclude' => $exclude],
            new ConversionCollection(),
            new Id('43c567e1396b4cadb52223a51796fd02')
        );

        $this->assertEquals($result, $campaign->checkFilters($keywords));
    }

    public function filteringDataProvider(): array
    {
        return [
            [[], [], [], true],
            [[], [], ['a' => ['a1', 'a2']], true],
            [[], ['a' => ['a1', 'a2'], 'b' => ['b1']], [], true],
            [['a' => ['a2', 'a3']], [], ['a' => ['a1', 'a2']], true],
            [[], ['a' => ['a3', 'a4']], ['a' => ['a1', 'a2']], true],
            [['a' => ['a2']], ['a' => ['a3'], 'b' => ['b1']], ['a' => ['a1', 'a2']], true],
            [['a' => ['a2', 'a3']], [], [], false],
            [['a' => ['a2', 'a3']], [], ['a' => ['a1', 'a4']], false],
            [[], ['a' => ['a1', 'a2'], 'b' => ['b1']], ['b' => ['b1']], false],
            [['a' => ['a1']], ['a' => ['a3'], 'b' => ['b1']], ['a' => ['a1'], 'b' => ['b1']], false],
            [['a' => ['a1']], ['a' => ['a3'], 'b' => ['b1']], ['a' => ['a1', 'a3']], false],
        ];
    }
}
