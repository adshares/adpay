<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Budget;
use PHPUnit\Framework\TestCase;

final class BudgetTest extends TestCase
{
    public function testInstanceOfId(): void
    {
        $budget = new Budget(100, 10, 20);

        $this->assertEquals(100, $budget->getBudget());
        $this->assertEquals(10, $budget->getMaxCpm());
        $this->assertEquals(20, $budget->getMaxCpc());

        $budget = new Budget(100, 0, 0);

        $this->assertEquals(100, $budget->getBudget());
        $this->assertEquals(0, $budget->getMaxCpm());
        $this->assertEquals(0, $budget->getMaxCpc());

        $budget = new Budget(200, null, null);

        $this->assertEquals(200, $budget->getBudget());
        $this->assertNull($budget->getMaxCpm());
        $this->assertNull($budget->getMaxCpc());
    }

    public function testInvalidZeroBudget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(0);
    }

    public function testInvalidMinusBudget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(-100);
    }

    public function testInvalidMinusMaxCpm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(100, -10);
    }

    public function testInvalidMinusMaxCpc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(100, null, -5);
    }
}
