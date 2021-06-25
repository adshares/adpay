<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class IdTest extends TestCase
{
    public function testInstanceOfId(): void
    {
        $value = '43c567e1396b4cadb52223a51796fd01';
        $id = new Id($value);

        $this->assertEquals($value, $id->toString());
        $this->assertEquals($value, (string)$id);
        $this->assertEquals(hex2bin($value), $id->toBin());
    }

    public function testInvalidEmptyId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('');
    }

    public function testInvalidId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('1234qwe');
    }

    public function testInvalidShortId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Id('43c567e1396b4cadb52223a');
    }

    public function testIsEquals(): void
    {
        $value = '43c567e1396b4cadb52223a51796fd01';

        $id = new Id($value);
        $id2 = new Id($value);
        $id3 = new Id('43c567e1396b4cadb52223a51796fd02');

        $this->assertTrue($id->equals($id));
        $this->assertTrue($id->equals($id2));
        $this->assertFalse($id->equals($id3));
        $this->assertFalse($id2->equals($id3));
    }

    public function testFromBin(): void
    {
        $value = '43c567e1396b4cadb52223a51796fd01';
        $bin = hex2bin($value);

        $id = Id::fromBin($bin);

        $this->assertEquals($value, $id->toString());
        $this->assertEquals($value, (string)$id);
        $this->assertEquals($bin, $id->toBin());
    }
}
