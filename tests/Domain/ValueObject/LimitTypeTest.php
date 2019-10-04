<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\LimitType;
use PHPUnit\Framework\TestCase;

final class LimitTypeTest extends TestCase
{
    public function testInBudgetType(): void
    {
        $eventType = LimitType::createInBudget();

        $this->assertEquals('in_budget', $eventType->toString());
        $this->assertEquals('in_budget', (string)$eventType);
        $this->assertTrue($eventType->isInBudget());
        $this->assertFalse($eventType->isOutOfBudget());
    }

    public function testOutOfBudgetType(): void
    {
        $eventType = LimitType::createOutOfBudget();

        $this->assertEquals('out_of_budget', $eventType->toString());
        $this->assertEquals('out_of_budget', (string)$eventType);
        $this->assertTrue($eventType->isOutOfBudget());
        $this->assertFalse($eventType->isInBudget());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LimitType('non-existent-type');
    }
}
