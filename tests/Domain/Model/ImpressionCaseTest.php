<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Impression;
use App\Domain\Model\ImpressionCase;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use PHPUnit\Framework\TestCase;

final class ImpressionCaseTest extends TestCase
{
    public function testInstanceOfImpressionCase(): void
    {
        $caseId = '43c567e1396b4cadb52223a51796fd01';
        $caseTime = '2019-01-01T12:00:00+10:00';
        $publisherId = 'ffc567e1396b4cadb52223a51796fd02';
        $zoneId = 'aac567e1396b4cadb52223a51796fdbb';
        $advertiserId = 'bbc567e1396b4cadb52223a51796fdaa';
        $campaignId = 'ccc567e1396b4cadb52223a51796fdcc';
        $bannerId = 'ddc567e1396b4cadb52223a51796fddd';

        $impressionId = '13c567e1396b4cadb52223a51796fd03';
        $trackingId = '23c567e1396b4cadb52223a51796fd02';
        $userId = '33c567e1396b4cadb52223a51796fd01';
        $keywords = ['k' => 111];
        $context = ['a' => 123];
        $humanScore = 0.89;
        $pageRank = 0.99;
        $adsTxt = 1;

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($humanScore, $pageRank, $adsTxt, $keywords, $context),
        );

        $case = new ImpressionCase(
            new Id($caseId),
            DateTimeHelper::fromString($caseTime),
            new Id($publisherId),
            new Id($zoneId),
            new Id($advertiserId),
            new Id($campaignId),
            new Id($bannerId),
            $impression
        );

        $this->assertInstanceOf(ImpressionCase::class, $case);
        $this->assertEquals($caseId, $case->getId());
        $this->assertEquals($publisherId, $case->getPublisherId());
        $this->assertEquals($zoneId, $case->getZoneId());
        $this->assertEquals($advertiserId, $case->getAdvertiserId());
        $this->assertEquals($campaignId, $case->getCampaignId());
        $this->assertEquals($bannerId, $case->getBannerId());
        $this->assertEquals($impression, $case->getImpression());
        $this->assertEquals($impressionId, $case->getImpressionId());
        $this->assertEquals($trackingId, $case->getTrackingId());
        $this->assertEquals($userId, $case->getUserId());
        $this->assertEquals($humanScore, $case->getHumanScore());
        $this->assertEquals($pageRank, $case->getPageRank());
        $this->assertEquals($keywords, $case->getKeywords());
        $this->assertEquals($context, $case->getContext()->getData());
        $this->assertEquals($context, $case->getContextData());

        $case = new ImpressionCase(
            new Id($caseId),
            DateTimeHelper::fromString($caseTime),
            new Id($publisherId),
            null,
            new Id($advertiserId),
            new Id($campaignId),
            new Id($bannerId),
            $impression
        );

        $this->assertNull($case->getZoneId());
    }
}
