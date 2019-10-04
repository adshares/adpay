<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\BannerType;
use PHPUnit\Framework\TestCase;

final class BannerTypeTest extends TestCase
{
    public function testClickType(): void
    {
        $eventType = BannerType::createImage();

        $this->assertEquals('image', $eventType->toString());
        $this->assertEquals('image', (string)$eventType);
        $this->assertTrue($eventType->isImage());
        $this->assertFalse($eventType->isHtml());
    }

    public function testConversionType(): void
    {
        $eventType = BannerType::createHtml();

        $this->assertEquals('html', $eventType->toString());
        $this->assertEquals('html', (string)$eventType);
        $this->assertTrue($eventType->isHtml());
        $this->assertFalse($eventType->isImage());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BannerType('non-existent-type');
    }
}
