<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\EventType;
use PHPUnit\Framework\TestCase;

final class EventTypeTest extends TestCase
{
    public function testClickType(): void
    {
        $type = EventType::createClick();

        $this->assertEquals('click', $type->toString());
        $this->assertEquals('click', (string)$type);
        $this->assertTrue($type->isClick());
        $this->assertFalse($type->isConversion());
        $this->assertFalse($type->isView());
    }

    public function testConversionType(): void
    {
        $type = EventType::createConversion();

        $this->assertEquals('conversion', $type->toString());
        $this->assertEquals('conversion', (string)$type);
        $this->assertTrue($type->isConversion());
        $this->assertFalse($type->isClick());
        $this->assertFalse($type->isView());
    }

    public function testViewType(): void
    {
        $type = EventType::createView();

        $this->assertEquals('view', $type->toString());
        $this->assertEquals('view', (string)$type);
        $this->assertTrue($type->isView());
        $this->assertFalse($type->isClick());
        $this->assertFalse($type->isConversion());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EventType('non-existent-type');
    }
}
