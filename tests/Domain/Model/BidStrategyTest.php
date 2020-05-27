<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\BidStrategy;
use Adshares\AdPay\Domain\ValueObject\Id;
use PHPUnit\Framework\TestCase;

final class BidStrategyTest extends TestCase
{
    public function testInstanceOfBidStrategy(): void
    {
        $bidStrategyId = '43c567e1396b4cadb52223a51796fd01';
        $category = 'user:country:st';
        $rank = 0.99;

        $bidStrategy = new BidStrategy(new Id($bidStrategyId), $category, $rank);

        $this->assertInstanceOf(BidStrategy::class, $bidStrategy);
        $this->assertEquals($bidStrategyId, $bidStrategy->getId());
        $this->assertEquals($category, $bidStrategy->getCategory());
        $this->assertEquals($rank, $bidStrategy->getRank());
    }

    public function testInvalidCategory(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $bidStrategyId = '43c567e1396b4cadb52223a51796fd01';
        $category = str_repeat('x', 268);
        $rank = 0.99;

        new BidStrategy(new Id($bidStrategyId), $category, $rank);
    }

    public function testInvalidRank(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $bidStrategyId = '43c567e1396b4cadb52223a51796fd01';
        $category = 'user:country:st';
        $rank = 2;

        new BidStrategy(new Id($bidStrategyId), $category, $rank);
    }
}
