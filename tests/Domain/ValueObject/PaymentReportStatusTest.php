<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;

final class PaymentReportStatusTest extends TestCase
{
    public function testPreparedStatus(): void
    {
        $status = PaymentReportStatus::createPrepared();

        $this->assertEquals(PaymentReportStatus::PREPARED, $status->getStatus());
        $this->assertEquals('prepared', $status->toString());
        $this->assertEquals('prepared', (string)$status);
        $this->assertTrue($status->isPrepared());
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
        $this->assertFalse($status->isPrepared());
    }

    public function testCompleteStatus(): void
    {
        $status = PaymentReportStatus::createComplete();

        $this->assertEquals(PaymentReportStatus::COMPLETE, $status->getStatus());
        $this->assertEquals('complete', $status->toString());
        $this->assertEquals('complete', (string)$status);
        $this->assertTrue($status->isComplete());
        $this->assertFalse($status->isIncomplete());
        $this->assertFalse($status->isPrepared());
    }

    public function testInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PaymentReportStatus(PHP_INT_MAX);
    }
}
