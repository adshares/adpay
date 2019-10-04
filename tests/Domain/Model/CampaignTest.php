<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\BannerCollection;
use Adshares\AdPay\Domain\Model\Campaign;
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

        $banners = new BannerCollection();

        $budgetValue = 1000000;
        $macCpm = 20;
        $maxCpc = 40;

        $budget = new Budget($budgetValue, $macCpm, $maxCpc);

        $campaign = new Campaign(
            new Id($campaignId),
            new Id($advertiserId),
            DateTimeHelper::createFromString($timeStart),
            DateTimeHelper::createFromString($timeEnd),
            $banners,
            [],
            $budget
        );

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals($campaignId, $campaign->getId());
        $this->assertEquals($advertiserId, $campaign->getAdvertiserId());
        $this->assertEquals($timeStart, $campaign->getTimeStart()->format(DateTime::ATOM));
        $this->assertEquals($timeEnd, $campaign->getTimeEnd()->format(DateTime::ATOM));
        $this->assertEquals($banners, $campaign->getBanners());
        $this->assertEquals(['require' => [], 'exclude' => []], $campaign->getFilters());
        $this->assertEmpty($campaign->getRequireFilters());
        $this->assertEmpty($campaign->getExcludeFilters());
        $this->assertEquals($budget, $campaign->getBudget());
        $this->assertEquals($budgetValue, $campaign->getBudgetValue());
        $this->assertEquals($macCpm, $campaign->getMaxCpm());
        $this->assertEquals($maxCpc, $campaign->getMaxCpc());
    }

    public function testInvalidTimeEnd(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Campaign(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            DateTimeHelper::createFromString('2019-01-02T12:00:00+00:00'),
            DateTimeHelper::createFromString('2019-01-01T12:00:00+00:00'),
            new BannerCollection(),
            [],
            new Budget(10)
        );
    }
}
