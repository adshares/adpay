<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\Conversion;
use Adshares\AdPay\Domain\Model\ConversionCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use PHPUnit\Framework\TestCase;

final class ConversionCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createConversion(1);
        $item2 = self::createConversion(2);
        $item3 = self::createConversion(3);
        $item4 = self::createConversion(4);

        $this->assertCount(4, new ConversionCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new ConversionCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createConversion(int $id): Conversion
    {
        return new Conversion(
            new Id('0000000000000000000000000000000'.(string)$id),
            new Id('43c567e1396b4cadb52223a51796fd01'),
            LimitType::createInBudget()
        );
    }
}
