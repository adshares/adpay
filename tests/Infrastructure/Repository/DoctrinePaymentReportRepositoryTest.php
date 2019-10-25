<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Infrastructure\Repository\DoctrinePaymentReportRepository;
use Adshares\AdPay\Infrastructure\Repository\DoctrinePaymentRepository;
use Adshares\AdPay\Lib\DateTimeHelper;
use DateTime;
use Psr\Log\NullLogger;

final class DoctrinePaymentReportRepositoryTest extends RepositoryTestCase
{
    public function testRepository(): void
    {
        $repository = new DoctrinePaymentReportRepository($this->connection, new NullLogger());

        $repository->save(new PaymentReport(1, PaymentReportStatus::createCalculated(), ['view' => [[2, 1000]]]));
        $repository->save(new PaymentReport(2, PaymentReportStatus::createCalculated()));
        $repository->save(new PaymentReport(3, PaymentReportStatus::createCalculated()));
        $repository->save(new PaymentReport(4, PaymentReportStatus::createIncomplete()));
        $repository->save(new PaymentReport(5, PaymentReportStatus::createIncomplete()));
        $repository->save(new PaymentReport(6, PaymentReportStatus::createComplete()));

        $report = $repository->fetch(1);
        $this->assertEquals(1, $report->getId());
        $this->assertEquals(PaymentReportStatus::CALCULATED, $report->getStatus()->getStatus());
        $this->assertEquals(
            [
                EventType::VIEW => [[2, 1000]],
                EventType::CLICK => [],
                EventType::CONVERSION => [],
            ],
            $report->getIntervals()
        );

        $this->assertCount(0, $repository->fetchByStatus());
        $this->assertCount(3, $repository->fetchByStatus(PaymentReportStatus::createCalculated()));
        $this->assertCount(2, $repository->fetchByStatus(PaymentReportStatus::createIncomplete()));
        $this->assertCount(1, $repository->fetchByStatus(PaymentReportStatus::createComplete()));
        $this->assertCount(
            5,
            $repository->fetchByStatus(
                PaymentReportStatus::createCalculated(),
                PaymentReportStatus::createIncomplete()
            )
        );
        $this->assertCount(
            6,
            $repository->fetchByStatus(
                PaymentReportStatus::createCalculated(),
                PaymentReportStatus::createIncomplete(),
                PaymentReportStatus::createComplete()
            )
        );
    }

    public function testFetchingNoneExistsReport(): void
    {
        $repository = new DoctrinePaymentReportRepository($this->connection, new NullLogger());

        $report = $repository->fetch(123);
        $this->assertEquals(123, $report->getId());
        $this->assertEquals(PaymentReportStatus::INCOMPLETE, $report->getStatus()->getStatus());
        $this->assertEquals(
            [
                EventType::VIEW => [],
                EventType::CLICK => [],
                EventType::CONVERSION => [],
            ],
            $report->getIntervals()
        );

        $this->assertCount(1, $repository->fetchByStatus(PaymentReportStatus::createIncomplete()));
    }

    public function testDeleting(): void
    {
        $timestamp = 1572004800;
        $repository = new DoctrinePaymentReportRepository($this->connection, new NullLogger());

        $repository->save(new PaymentReport($timestamp, PaymentReportStatus::createCalculated()));
        $repository->save(new PaymentReport($timestamp - 3600, PaymentReportStatus::createCalculated()));
        $repository->save(new PaymentReport($timestamp - 7200, PaymentReportStatus::createIncomplete()));
        $repository->save(new PaymentReport($timestamp - 10800, PaymentReportStatus::createComplete()));
        $repository->save(new PaymentReport($timestamp - 14400, PaymentReportStatus::createCalculated()));

        $paymentRepository = new DoctrinePaymentRepository($this->connection, new NullLogger());
        $paymentRepository->saveAllRaw(
            $timestamp,
            [
                ['event_id' => 'fffa', 'event_type' => 'view', 'status' => 0, 'value' => 100],
                ['event_id' => 'ffff', 'event_type' => 'view', 'status' => 0, 'value' => 100],
            ]
        );

        $this->assertEquals(
            0,
            $repository->deleteByTime(DateTimeHelper::fromTimestamp($timestamp + 1))
        );
        $this->assertCount(
            5,
            $repository->fetchByStatus(
                PaymentReportStatus::createCalculated(),
                PaymentReportStatus::createIncomplete(),
                PaymentReportStatus::createComplete()
            )
        );
        $this->assertCount(2, self::iterableToArray($paymentRepository->fetchByReportId($timestamp)));
        $this->assertEquals(
            3,
            $repository->deleteByTime(
                DateTimeHelper::fromTimestamp($timestamp - 10800),
                DateTimeHelper::fromTimestamp($timestamp - 3600)
            )
        );
        $this->assertCount(
            2,
            $repository->fetchByStatus(
                PaymentReportStatus::createCalculated(),
                PaymentReportStatus::createIncomplete(),
                PaymentReportStatus::createComplete()
            )
        );
        $this->assertCount(2, self::iterableToArray($paymentRepository->fetchByReportId($timestamp)));
        $this->assertEquals(
            2,
            $repository->deleteByTime(null, DateTimeHelper::fromTimestamp($timestamp))
        );
        $this->assertEmpty(
            $repository->fetchByStatus(
                PaymentReportStatus::createCalculated(),
                PaymentReportStatus::createIncomplete(),
                PaymentReportStatus::createComplete()
            )
        );
        $this->assertEmpty(self::iterableToArray($paymentRepository->fetchByReportId($timestamp)));
    }

    public function testSavingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentReportRepository($this->failedConnection(), new NullLogger());
        $repository->save(new PaymentReport(1, PaymentReportStatus::createCalculated()));
    }

    public function testFetchingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentReportRepository($this->failedConnection(), new NullLogger());
        $repository->fetch(1);
    }

    public function testFetchingByStatusException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentReportRepository($this->failedConnection(), new NullLogger());
        $repository->fetchByStatus(PaymentReportStatus::createCalculated());
    }

    public function testDeleteingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentReportRepository($this->failedConnection(), new NullLogger());
        $repository->deleteByTime(new DateTime());
    }
}
