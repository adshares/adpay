<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\EventType;
use Adshares\AdPay\Domain\Model\ClickEvent;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ClickEventTest extends TestCase
{
    public function testInstanceOfBanner(): void
    {
        $eventId = '43c567e1396b4cadb52223a51796fd01';

        $event =
            new ClickEvent(new Id($eventId));

        $this->assertInstanceOf(ClickEvent::class, $event);
        $this->assertEquals($eventId, $event->getId());
        $this->assertEquals(EventType::CLICK, $event->getType());
    }
}
