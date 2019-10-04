<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Limit;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use PHPUnit\Framework\TestCase;

final class LimitTest extends TestCase
{
    public function testInstanceOfLimit(): void
    {
        $typeIn = LimitType::createInBudget();
        $typeOut = LimitType::createOutOfBudget();

        $limit = new Limit(100, $typeIn, 20);

        $this->assertEquals(100, $limit->getValue());
        $this->assertEquals(LimitType::IN_BUDGET, $limit->getType());
        $this->assertEquals(20, $limit->getCost());
        $this->assertEquals('100 [in_budget]', $limit->toString());
        $this->assertEquals('100 [in_budget]', (string)$limit);

        $limit = new Limit(null, $typeIn);

        $this->assertNull($limit->getValue());
        $this->assertEquals(LimitType::IN_BUDGET, $limit->getType());
        $this->assertEquals(0, $limit->getCost());
        $this->assertEquals('- [in_budget]', (string)$limit);

        $limit = new Limit(0, $typeOut);

        $this->assertEquals(0, $limit->getValue());
        $this->assertEquals(LimitType::OUT_OF_BUDGET, $limit->getType());
        $this->assertEquals(0, $limit->getCost());
        $this->assertEquals('0 [out_of_budget]', (string)$limit);

        $limit = new Limit(200, $typeOut, 0);

        $this->assertEquals(200, $limit->getValue());
        $this->assertEquals(LimitType::OUT_OF_BUDGET, $limit->getType());
        $this->assertEquals(0, $limit->getCost());
        $this->assertEquals('200 [out_of_budget]', (string)$limit);
    }

    public function testInvalidNegativeLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Limit(-100, LimitType::createInBudget());
    }

    public function testInvalidNegativeCost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Limit(100, LimitType::createInBudget(), -200);
    }
}
