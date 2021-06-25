<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\BannerType;
use PHPUnit\Framework\TestCase;

final class BannerTypeTest extends TestCase
{
    public function testImageType(): void
    {
        $type = BannerType::createImage();

        $this->assertEquals('image', $type->toString());
        $this->assertEquals('image', (string)$type);
        $this->assertTrue($type->isImage());
        $this->assertFalse($type->isHtml());
    }

    public function testHtmlType(): void
    {
        $type = BannerType::createHtml();

        $this->assertEquals('html', $type->toString());
        $this->assertEquals('html', (string)$type);
        $this->assertTrue($type->isHtml());
        $this->assertFalse($type->isImage());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BannerType('non-existent-type');
    }
}
