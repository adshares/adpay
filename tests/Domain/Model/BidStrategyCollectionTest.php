<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\Model\BidStrategyCollection;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class BidStrategyCollectionTest extends TestCase
{
    public function testMultiplyAdding(): void
    {
        $item1 = self::createBidStrategy(1);
        $item2 = self::createBidStrategy(2);
        $item3 = self::createBidStrategy(3);
        $item4 = self::createBidStrategy(4);

        $this->assertCount(4, new BidStrategyCollection($item1, $item2, $item3, $item4));
    }

    public function testEmptyCollection(): void
    {
        $collection = new BidStrategyCollection();

        $this->assertCount(0, $collection);
        $this->assertEmpty($collection);
    }

    private static function createBidStrategy(int $id): BidStrategy
    {
        return new BidStrategy(
            new Id(str_pad((string)$id, 32, '0', STR_PAD_LEFT)),
            'user:country:st',
            0.99
        );
    }
}
