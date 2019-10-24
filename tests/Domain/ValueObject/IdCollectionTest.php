<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\IdCollection;
use PHPUnit\Framework\TestCase;

final class IdCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $id1 = '00000000000000000000000000000001';
        $id2 = '00000000000000000000000000000002';
        $id3 = '00000000000000000000000000000003';
        $id4 = '00000000000000000000000000000004';

        $collection = new IdCollection(
            new Id($id1),
            new Id($id2),
            new Id($id3),
            new Id($id4)
        );

        $this->assertCount(4, $collection);
        $this->assertEquals([hex2bin($id1), hex2bin($id2), hex2bin($id3), hex2bin($id4)], $collection->toBinArray());
    }

    public function testEmptyCollection(): void
    {
        $collection = new IdCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }
}
