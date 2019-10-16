<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;

final class PaymentReportTest extends TestCase
{
    public function testInstanceOfPaymentReport(): void
    {
        $id = 1571011200;
        $status = new PaymentReportStatus();
        $intervals = [EventType::VIEW => [[10, 20]]];

        $emptyIntervals = [
            EventType::VIEW => [],
            EventType::CLICK => [],
            EventType::CONVERSION => [],
        ];

        $report = new PaymentReport($id, $status, $intervals);

        $this->assertInstanceOf(PaymentReport::class, $report);
        $this->assertEquals($id, $report->getId());
        $this->assertEquals($status, $report->getStatus());
        $this->assertEquals(array_merge($emptyIntervals, $intervals), $report->getIntervals());
        $this->assertEquals([[10, 20]], $report->getTypedIntervals(EventType::createView()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createClick()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createConversion()));
        $this->assertFalse($report->isComplete());
        $this->assertFalse($report->isCalculated());

        $report = new PaymentReport($id, $status);
        $this->assertEquals($emptyIntervals, $report->getIntervals());
        $this->assertEmpty($report->getTypedIntervals(EventType::createView()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createClick()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createConversion()));

        $report = new PaymentReport($id, $status, ['_invalid_' => [[1, 2]]]);
        $this->assertEquals($emptyIntervals, $report->getIntervals());
    }

    public function testInvalidConstructorIntervals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PaymentReport(1571011200, new PaymentReportStatus(), [EventType::VIEW => [1]]);
    }

    public function testMalformedIntervals(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $report = new PaymentReport(1571011200, new PaymentReportStatus());
        $report->addIntervals(EventType::createView(), [1]);
    }

    public function testMalformedInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $report = new PaymentReport(1571011200, new PaymentReportStatus());
        $report->addIntervals(EventType::createView(), [[1]]);
    }

    public function testTooLowInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $report = new PaymentReport(1571011200, new PaymentReportStatus());
        $report->addInterval(EventType::createView(), -100, 100);
    }

    public function testTooHighInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $report = new PaymentReport(1571011200, new PaymentReportStatus());
        $report->addInterval(EventType::createView(), 100, 10000);
    }

    public function testInvalidInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $report = new PaymentReport(1571011200, new PaymentReportStatus());
        $report->addInterval(EventType::createView(), 100, 99);
    }

    public function testAddIntervals(): void
    {
        $type = EventType::createView();
        $report = new PaymentReport(1571011200, new PaymentReportStatus(), [(string)$type => [[20, 21], [2, 18]]]);
        $this->assertEquals([[2, 18], [20, 21]], $report->getTypedIntervals($type));

        $report->addIntervals($type, [[19, 19]]);
        $this->assertEquals([[2, 21]], $report->getTypedIntervals($type));

        $report->addIntervals($type, [[30, 59], [0, 15], [50, 55], [58, 61]]);
        $this->assertEquals([[0, 21], [30, 61]], $report->getTypedIntervals($type));

        $report->addInterval($type, 50, 99);
        $this->assertEquals([[0, 21], [30, 99]], $report->getTypedIntervals($type));

        $report->addInterval($type, 25, 25);
        $this->assertEquals([[0, 21], [25, 25], [30, 99]], $report->getTypedIntervals($type));

        $report->addInterval($type, 22, 22);
        $this->assertEquals([[0, 22], [25, 25], [30, 99]], $report->getTypedIntervals($type));

        $report->addInterval($type, 23, 24);
        $this->assertEquals([[0, 25], [30, 99]], $report->getTypedIntervals($type));

        $report->addInterval($type, 27, 101);
        $this->assertEquals([[0, 25], [27, 101]], $report->getTypedIntervals($type));

        $report->addInterval($type, 20, 150);
        $this->assertEquals([[0, 150]], $report->getTypedIntervals($type));
    }

    public function testCompleteInterval(): void
    {
        $type = EventType::createView();

        $report = new PaymentReport(1571011200, PaymentReportStatus::createCalculated());
        $this->assertTrue($report->isComplete());

        $report->addInterval($type, 50, 99);
        $this->assertTrue($report->isComplete());

        $report = new PaymentReport(1571011200, PaymentReportStatus::createComplete());
        $this->assertTrue($report->isComplete());

        $report->addInterval($type, 50, 99);
        $this->assertTrue($report->isComplete());

        $report = new PaymentReport(1571011200, PaymentReportStatus::createIncomplete());
        $this->assertFalse($report->isComplete());

        $report->addInterval(EventType::createClick(), 0, 3599);
        $this->assertFalse($report->isComplete());

        $report->addInterval(EventType::createConversion(), 0, 3599);
        $this->assertFalse($report->isComplete());

        $report->addInterval($type, 50, 99);
        $this->assertFalse($report->isComplete());

        $report->addIntervals($type, [[0, 2000], [2002, 3599]]);
        $this->assertFalse($report->isComplete());

        $report->addInterval($type, 2001, 2001);
        $this->assertTrue($report->isComplete());
    }

    public function testTimestampToId()
    {
        $this->assertEquals(0, PaymentReport::timestampToId(22));
    }
}
