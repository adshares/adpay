<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Size;
use PHPUnit\Framework\TestCase;

final class SizeTest extends TestCase
{
    public function testFromString(): void
    {
        $size = Size::fromString('200x65');

        $this->assertEquals(200, $size->getWidth());
        $this->assertEquals(65, $size->getHeight());
        $this->assertEquals('200x65', $size->toString());
        $this->assertEquals('200x65', (string)$size);
    }

    public function testZeroDimensions(): void
    {
        $size = Size::fromString('0' . 'x' . '0');
        $this->assertEquals(0, $size->getWidth());
        $this->assertEquals(0, $size->getHeight());
    }

    public function testInvalidEmptyFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Size::fromString('');
    }

    public function testInvalidNoDimensionsFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Size::fromString('x');
    }

    public function testInvalidNoNumberFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Size::fromString('w100x90');
    }

    public function testInvalidOnlyWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Size::fromString('20065');
    }

    public function testInvalidOnlyHeight(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Size::fromString('x20065');
    }
}
