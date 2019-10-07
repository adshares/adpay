<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Budget;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTime;
use PHPUnit\Framework\TestCase;

final class CampaignTest extends TestCase
{
    public function testInstanceOfCampaign(): void
    {
        $campaignId = '43c567e1396b4cadb52223a51796fd01';
        $advertiserId = 'ffc567e1396b4cadb52223a51796fd02';
        $timeStart = '2019-01-01T12:00:00+00:00';
        $timeEnd = '2019-03-03T09:00:00+00:00';

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
            DateTimeHelper::createFromString($timeStart),
            DateTimeHelper::createFromString($timeEnd),
            $budget,
            $banners,
            $filters,
            $conversions
        );

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals($campaignId, $campaign->getId());
        $this->assertEquals($advertiserId, $campaign->getAdvertiserId());
        $this->assertEquals($timeStart, $campaign->getTimeStart()->format(DateTime::ATOM));
        $this->assertEquals($timeEnd, $campaign->getTimeEnd()->format(DateTime::ATOM));
        $this->assertEquals($budget, $campaign->getBudget());
        $this->assertEquals($budgetValue, $campaign->getBudgetValue());
        $this->assertEquals($macCpm, $campaign->getMaxCpm());
        $this->assertEquals($maxCpc, $campaign->getMaxCpc());
        $this->assertEquals($banners, $campaign->getBanners());
        $this->assertEquals($filters, $campaign->getFilters());
        $this->assertEquals($conversions, $campaign->getConversions());
    }

    public function testEmptyFilters(): void
    {
        $campaign = new Campaign(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd0f'),
            new DateTime(),
            new DateTime(),
            new Budget(100),
            new BannerCollection(),
            [],
            new ConversionCollection()
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
            DateTimeHelper::createFromString('2019-01-02T12:00:00+00:00'),
            DateTimeHelper::createFromString('2019-01-01T12:00:00+00:00'),
            new Budget(10),
            new BannerCollection(),
            [],
            new ConversionCollection()
        );
    }
}
