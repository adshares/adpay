<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Payment;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentTest extends TestCase
{
    public function testInstanceOfPayment(): void
    {
        $reportId = 123;
        $eventId = '43c567e1396b4cadb52223a51796fd01';
        $status = PaymentStatus::INVALID_TARGETING;
        $value = 100;

        $payment =
            new Payment(
                EventType::createView(),
                new Id($eventId),
                new PaymentStatus($status)
            );

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(EventType::VIEW, $payment->getEventType());
        $this->assertEquals($eventId, $payment->getEventId());
        $this->assertEquals($status, $payment->getStatus()->getStatus());
        $this->assertEquals($status, $payment->getStatusCode());
        $this->assertNull($payment->getValue());

        $payment =
            new Payment(
                EventType::createView(),
                new Id($eventId),
                new PaymentStatus($status),
                $value,
                $reportId
            );

        $this->assertEquals($value, $payment->getValue());
        $this->assertEquals($reportId, $payment->getReportId());
    }

    public function testNullReportId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $payment =
            new Payment(
                EventType::createView(),
                new Id('43c567e1396b4cadb52223a51796fd01'),
                new PaymentStatus(PaymentStatus::ACCEPTED)
            );

        $payment->getReportId();
    }
}
