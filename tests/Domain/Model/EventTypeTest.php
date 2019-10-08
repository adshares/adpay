<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\EventType;
use PHPUnit\Framework\TestCase;

final class EventTypeTest extends TestCase
{
    public function testClickType(): void
    {
        $eventType = EventType::createClick();

        $this->assertEquals('click', $eventType->toString());
        $this->assertEquals('click', (string)$eventType);
        $this->assertTrue($eventType->isClick());
        $this->assertFalse($eventType->isConversion());
        $this->assertFalse($eventType->isView());
    }

    public function testConversionType(): void
    {
        $eventType = EventType::createConversion();

        $this->assertEquals('conversion', $eventType->toString());
        $this->assertEquals('conversion', (string)$eventType);
        $this->assertTrue($eventType->isConversion());
        $this->assertFalse($eventType->isClick());
        $this->assertFalse($eventType->isView());
    }

    public function testViewType(): void
    {
        $eventType = EventType::createView();

        $this->assertEquals('view', $eventType->toString());
        $this->assertEquals('view', (string)$eventType);
        $this->assertTrue($eventType->isView());
        $this->assertFalse($eventType->isClick());
        $this->assertFalse($eventType->isConversion());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EventType('non-existent-type');
    }
}
