<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\Model\ConversionEvent;
use App\Domain\Model\Impression;
use App\Domain\Model\ImpressionCase;
use App\Domain\ValueObject\Context;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PaymentStatus;
use App\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class ConversionEventTest extends TestCase
{
    public function testInstanceOfConversionEvent(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';
        $time = '2019-01-01T12:00:00+00:00';
        $groupId = '66c567e1396b4cadb52223a51796fd05';
        $conversionId = '53c567e1396b4cadb52223a51796fd05';
        $conversionValue = 123;

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

        $event = new ConversionEvent(
            new Id($eventId),
            DateTimeHelper::fromString($time),
            $case,
            new Id($groupId),
            new Id($conversionId),
            $conversionValue
        );

        $this->assertInstanceOf(ConversionEvent::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::CONVERSION, $event->getType());
        $this->assertEquals($time, $event->getTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($case, $event->getCase());
        $this->assertEquals(PaymentStatus::ACCEPTED, $event->getPaymentStatus()->getStatus());
        $this->assertEquals($groupId, $event->getGroupId());
        $this->assertEquals($conversionId, $event->getConversionId());
        $this->assertEquals($conversionValue, $event->getConversionValue());
        $this->assertNull($event->getPaymentStatus()->getStatus());

        $event = new ConversionEvent(
            new Id($eventId),
            DateTimeHelper::fromString($time),
            $case,
            new Id($groupId),
            new Id($conversionId),
            $conversionValue,
            new PaymentStatus(PaymentStatus::ACCEPTED)
        );

        $this->assertEquals(PaymentStatus::ACCEPTED, $event->getPaymentStatus()->getStatus());
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $event = new ConversionEvent(
            new Id('43c567e1396b4cadb52223a51796fd01'),
            DateTimeHelper::fromTimestamp(123123123),
            new ImpressionCase(
                new Id('43c567e1396b4cadb52223a51796fd01'),
                DateTimeHelper::fromTimestamp(123123123),
                new Id('ffc567e1396b4cadb52223a51796fd02'),
                new Id('aac567e1396b4cadb52223a51796fdbb'),
                new Id('bbc567e1396b4cadb52223a51796fdaa'),
                new Id('ccc567e1396b4cadb52223a51796fdcc'),
                new Id('ddc567e1396b4cadb52223a51796fddd'),
                new Impression(
                    new Id('13c567e1396b4cadb52223a51796fd03'),
                    new Id('23c567e1396b4cadb52223a51796fd02'),
                    new Id('33c567e1396b4cadb52223a51796fd01'),
                    new Context(0.89, 0.99)
                )
            ),
            new Id('66c567e1396b4cadb52223a51796fd05'),
            new Id('53c567e1396b4cadb52223a51796fd05'),
            -1
        );
    }
}
