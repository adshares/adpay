<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Model\Impression;
use App\Domain\Model\ImpressionCase;
use App\Domain\Model\ViewEvent;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PaymentStatus;
use App\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class ViewEventTest extends TestCase
{
    public function testInstanceOfViewEvent(): void
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

        $event = new ViewEvent(
            new Id($eventId),
            DateTimeHelper::fromString($time),
            $case
        );

        $this->assertInstanceOf(ViewEvent::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::VIEW, $event->getType());
        $this->assertEquals($time, $event->getTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($case, $event->getCase());
    }
}
