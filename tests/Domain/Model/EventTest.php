<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    public function testInstanceOfEvent(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';
        $time = '2019-01-01T12:00:00+00:00';

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

        /* @var $event Event */
        $event = $this->getMockForAbstractClass(
            'Adshares\AdPay\Domain\Model\Event',
            [
                new Id($eventId),
                EventType::createView(),
                DateTimeHelper::createFromString($time),
                $case,
            ]
        );

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::VIEW, $event->getType());
        $this->assertEquals($time, $event->getTime()->format(DateTimeInterface::ATOM));
        $this->assertEquals($case, $event->getCase());
        $this->assertEquals($impressionCaseId, $event->getCaseId());
        $this->assertEquals($publisherId, $event->getPublisherId());
        $this->assertEquals($zoneId, $event->getZoneId());
        $this->assertEquals($advertiserId, $event->getAdvertiserId());
        $this->assertEquals($campaignId, $event->getCampaignId());
        $this->assertEquals($bannerId, $event->getBannerId());
        $this->assertEquals($impression, $event->getImpression());
        $this->assertEquals($impressionId, $event->getImpressionId());
        $this->assertEquals($trackingId, $event->getTrackingId());
        $this->assertEquals($userId, $event->getUserId());
        $this->assertEquals($context, $event->getContext()->all());
        $this->assertEquals($context, $event->getContextData());
        $this->assertEquals($humanScore, $event->getHumanScore());
        $this->assertNull($event->getPaymentStatus()->getStatus());

        /* @var $event Event */
        $event = $this->getMockForAbstractClass(
            'Adshares\AdPay\Domain\Model\Event',
            [
                new Id($eventId),
                EventType::createView(),
                DateTimeHelper::createFromString($time),
                $case,
                new PaymentStatus(PaymentStatus::ACCEPTED),
            ]
        );

        $this->assertEquals(PaymentStatus::ACCEPTED, $event->getPaymentStatus()->getStatus());
    }
}
