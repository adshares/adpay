<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class ConversionEventTest extends TestCase
{
    public function testInstanceOfConversionEvent(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';
        $time = '2019-01-01T12:00:00+00:00';
        $conversionId = '53c567e1396b4cadb52223a51796fd05';
        $conversionValue = 123;

        $impressionCaseId = '43c567e1396b4cadb52223a51796fd01';
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
        $humanScore = 0.99;

        $impression = new Impression(
            new Id($impressionId),
            new Id($trackingId),
            new Id($userId),
            new Context($humanScore, $keywords, $context)
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

        $event = new ConversionEvent(
            new Id($eventId),
            DateTimeHelper::createFromString($time),
            $case,
            new Id($conversionId),
            $conversionValue,
            new PaymentStatus(PaymentStatus::ACCEPTED)
        );

        $this->assertInstanceOf(ConversionEvent::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::CONVERSION, $event->getType());
        $this->assertEquals($time, $event->getTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($case, $event->getCase());
        $this->assertEquals(PaymentStatus::ACCEPTED, $event->getPaymentStatus()->getStatus());
        $this->assertEquals($conversionId, $event->getConversionId());
        $this->assertEquals($conversionValue, $event->getConversionValue());

        $event = new ConversionEvent(
            new Id($eventId),
            DateTimeHelper::createFromString($time),
            $case,
            new Id($conversionId),
            null,
            new PaymentStatus(PaymentStatus::ACCEPTED)
        );

        $this->assertNull($event->getConversionValue());
    }
}
