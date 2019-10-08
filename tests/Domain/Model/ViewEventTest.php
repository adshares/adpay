<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\EventType;
use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ViewEventTest extends TestCase
{
    public function testInstanceOfBanner(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';

        $event =
            new ViewEvent(new Id($eventId));

        $this->assertInstanceOf(ViewEvent::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::VIEW, $event->getType());
    }
}
