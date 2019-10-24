<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;

final class PaymentReportStatusTest extends TestCase
{
    public function testCalculatedStatus(): void
    {
        $status = PaymentReportStatus::createCalculated();

        $this->assertEquals(PaymentReportStatus::CALCULATED, $status->getStatus());
        $this->assertEquals('calculated', $status->toString());
        $this->assertEquals('calculated', (string)$status);
        $this->assertTrue($status->isCalculated());
        $this->assertTrue($status->isComplete());
        $this->assertFalse($status->isIncomplete());
    }

    public function testIncompleteStatus(): void
    {
        $status = PaymentReportStatus::createIncomplete();

        $this->assertEquals(PaymentReportStatus::INCOMPLETE, $status->getStatus());
        $this->assertEquals('incomplete', $status->toString());
        $this->assertEquals('incomplete', (string)$status);
        $this->assertTrue($status->isIncomplete());
        $this->assertFalse($status->isComplete());
        $this->assertFalse($status->isCalculated());
    }

    public function testCompleteStatus(): void
    {
        $status = PaymentReportStatus::createComplete();

        $this->assertEquals(PaymentReportStatus::COMPLETE, $status->getStatus());
        $this->assertEquals('complete', $status->toString());
        $this->assertEquals('complete', (string)$status);
        $this->assertTrue($status->isComplete());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isCalculated());
    }

    public function testInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaymentReportStatus(PHP_INT_MAX);
    }
}
