<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ImpressionCaseTest extends TestCase
{
    public function testInstanceOfImpressionCase(): void
    {
        $impressionCaseId = '43c567e1396b4cadb52223a51796fd01';
        $publisherId = 'ffc567e1396b4cadb52223a51796fd02';
        $zoneId = 'aac567e1396b4cadb52223a51796fdbb';
        $advertiserId = 'bbc567e1396b4cadb52223a51796fdaa';
        $campaignId = 'ccc567e1396b4cadb52223a51796fdcc';
        $bannerId = 'ddc567e1396b4cadb52223a51796fddd';

        $impressionId = '13c567e1396b4cadb52223a51796fd03';
        $trackingId = '23c567e1396b4cadb52223a51796fd02';
        $userId = '33c567e1396b4cadb52223a51796fd01';
        $context = ['a' => 123];
        $humanScore = 0.99;

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($context),
            $humanScore
        );

        $case = new ImpressionCase(
            new Id($impressionCaseId),
            new Id($publisherId),
            new Id($zoneId),
            new Id($advertiserId),
            new Id($campaignId),
            new Id($bannerId),
            $impression
        );

        $this->assertInstanceOf(ImpressionCase::class, $case);
        $this->assertEquals($impressionCaseId, $case->getId());
        $this->assertEquals($publisherId, $case->getPublisherId());
        $this->assertEquals($zoneId, $case->getZoneId());
        $this->assertEquals($advertiserId, $case->getAdvertiserId());
        $this->assertEquals($campaignId, $case->getCampaignId());
        $this->assertEquals($bannerId, $case->getBannerId());
        $this->assertEquals($impression, $case->getImpression());
        $this->assertEquals($impressionId, $case->getImpressionId());
        $this->assertEquals($trackingId, $case->getTrackingId());
        $this->assertEquals($userId, $case->getUserId());
        $this->assertEquals($context, $case->getContext()->all());
        $this->assertEquals($context, $case->getContextData());
        $this->assertEquals($humanScore, $case->getHumanScore());
    }
}
