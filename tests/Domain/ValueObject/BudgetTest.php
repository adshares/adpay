<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\Budget;
use PHPUnit\Framework\TestCase;

final class BudgetTest extends TestCase
{
    public function testInstanceOfBudget(): void
    {
        $budget = new Budget(100, 10, 20);

        $this->assertEquals(100, $budget->getValue());
        $this->assertEquals(10, $budget->getMaxCpm());
        $this->assertEquals(20, $budget->getMaxCpc());
        $this->assertEquals('100 [10/20]', $budget->toString());
        $this->assertEquals('100 [10/20]', (string)$budget);

        $budget = new Budget(200, 0, 0);

        $this->assertEquals(200, $budget->getValue());
        $this->assertEquals(0, $budget->getMaxCpm());
        $this->assertEquals(0, $budget->getMaxCpc());
        $this->assertEquals('200 [0/0]', (string)$budget);

        $budget = new Budget(300, 30, null);

        $this->assertEquals(300, $budget->getValue());
        $this->assertEquals(30, $budget->getMaxCpm());
        $this->assertNull($budget->getMaxCpc());
        $this->assertEquals('300 [30/-]', (string)$budget);

        $budget = new Budget(400, null, 40);

        $this->assertEquals(400, $budget->getValue());
        $this->assertNull($budget->getMaxCpm());
        $this->assertEquals(40, $budget->getMaxCpc());
        $this->assertEquals('400 [-/40]', (string)$budget);

        $budget = new Budget(500, null, null);

        $this->assertEquals(500, $budget->getValue());
        $this->assertNull($budget->getMaxCpm());
        $this->assertNull($budget->getMaxCpc());
        $this->assertEquals('500 [-/-]', (string)$budget);
    }

    public function testInvalidZeroBudget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(0);
    }

    public function testInvalidNegativeBudget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(-100);
    }

    public function testInvalidNegativeMaxCpm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(100, -10);
    }

    public function testInvalidNegativeMaxCpc(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Budget(100, null, -5);
    }
}
