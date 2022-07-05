<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Event;
use App\Domain\Model\Impression;
use App\Domain\Model\ImpressionCase;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    public function testInstanceOfEvent(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';
        $time = '2019-01-01T12:00:00+00:00';

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

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($humanScore, $pageRank, $keywords, $context)
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

        /* @var $event Event */
        $event = $this->getMockForAbstractClass(
            Event::class,
            [
                new Id($eventId),
                EventType::createView(),
                DateTimeHelper::fromString($time),
                $case,
            ]
        );

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::VIEW, $event->getType());
        $this->assertEquals($time, $event->getTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($case, $event->getCase());
        $this->assertEquals($caseId, $event->getCaseId());
        $this->assertEquals($caseTime, $event->getCaseTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($publisherId, $event->getPublisherId());
        $this->assertEquals($zoneId, $event->getZoneId());
        $this->assertEquals($advertiserId, $event->getAdvertiserId());
        $this->assertEquals($campaignId, $event->getCampaignId());
        $this->assertEquals($bannerId, $event->getBannerId());
        $this->assertEquals($impression, $event->getImpression());
        $this->assertEquals($impressionId, $event->getImpressionId());
        $this->assertEquals($trackingId, $event->getTrackingId());
        $this->assertEquals($userId, $event->getUserId());
        $this->assertEquals($keywords, $event->getKeywords());
        $this->assertEquals($humanScore, $event->getHumanScore());
        $this->assertEquals($pageRank, $event->getPageRank());
        $this->assertEquals($context, $event->getContext()->getData());
        $this->assertEquals($context, $event->getContextData());

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

        /* @var $event Event */
        $event = $this->getMockForAbstractClass(
            Event::class,
            [
                new Id($eventId),
                EventType::createView(),
                DateTimeHelper::fromString($time),
                $case
            ]
        );

        $this->assertNull($event->getZoneId());
    }
}
