<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\ConversionEventCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class ConversionEventCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createConversionEvent(1);
        $item2 = self::createConversionEvent(2);
        $item3 = self::createConversionEvent(3);
        $item4 = self::createConversionEvent(4);

        $this->assertCount(4, new ConversionEventCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new ConversionEventCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createConversionEvent(int $id): ConversionEvent
    {
        return new ConversionEvent(
            new Id('0000000000000000000000000000000'.(string)$id)
        );
    }
}
