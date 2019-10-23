<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Infrastructure\Repository;

use Adshares\AdPay\Domain\Exception\DomainRepositoryException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use Adshares\AdPay\Infrastructure\Repository\DoctrinePaymentReportRepository;
use Adshares\AdPay\Infrastructure\Repository\DoctrinePaymentRepository;
use Psr\Log\NullLogger;

final class DoctrinePaymentRepositoryTest extends RepositoryTestCase
{
    public function testRepository(): void
    {
        $this->setUpReports(1, 2, 3, 4);
        $repository = new DoctrinePaymentRepository($this->connection, new NullLogger());

        $repository->saveAllRaw(
            1,
            [
                self::payment(12),
                self::payment(13),
                self::payment(14),
                self::payment(15),
                self::payment(16),
            ]
        );
        $repository->saveAllRaw(
            2,
            [
                self::payment(22),
                self::payment(23),
            ]
        );
        $repository->saveAllRaw(
            3,
            [
                self::payment(32),
                self::payment(33),
                self::payment(34),
            ]
        );

        $repository->saveAllRaw(4, []);

        $this->assertCount(5, self::iterableToArray($repository->fetchByReportId(1)));
        $this->assertCount(2, self::iterableToArray($repository->fetchByReportId(2)));
        $this->assertCount(3, self::iterableToArray($repository->fetchByReportId(3)));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(4)));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(5)));
    }

    public function testPagination(): void
    {
        $this->setUpReports(1);
        $repository = new DoctrinePaymentRepository($this->connection, new NullLogger());

        $repository->saveAllRaw(
            1,
            [
                self::payment(11),
                self::payment(12),
                self::payment(13),
                self::payment(14),
                self::payment(15),
                self::payment(16),
                self::payment(17),
            ]
        );

        $this->assertCount(3, self::iterableToArray($repository->fetchByReportId(1, 3)));
        $this->assertCount(3, self::iterableToArray($repository->fetchByReportId(1, 3, 3)));
        $this->assertCount(1, self::iterableToArray($repository->fetchByReportId(1, 3, 6)));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(1, 3, 9)));
    }

    public function testDeleting(): void
    {
        $this->setUpReports(1, 2);
        $repository = new DoctrinePaymentRepository($this->connection, new NullLogger());

        $repository->saveAllRaw(
            1,
            [
                self::payment(12),
                self::payment(13),
            ]
        );

        $repository->saveAllRaw(
            2,
            [
                self::payment(22),
            ]
        );

        $this->assertEquals(0, $repository->deleteByReportId(123));
        $this->assertCount(2, self::iterableToArray($repository->fetchByReportId(1)));
        $this->assertCount(1, self::iterableToArray($repository->fetchByReportId(2)));

        $this->assertEquals(2, $repository->deleteByReportId(1));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(1)));
        $this->assertCount(1, self::iterableToArray($repository->fetchByReportId(2)));

        $this->assertEquals(1, $repository->deleteByReportId(2));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(1)));
        $this->assertEmpty(self::iterableToArray($repository->fetchByReportId(2)));
    }

    public function testSavingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentRepository($this->failedConnection(), new NullLogger());
        $repository->saveAllRaw(1, [self::payment(11)]);
    }

    public function testFetchingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentRepository($this->failedConnection(), new NullLogger());
        self::iterableToArray($repository->fetchByReportId(2));
    }

    public function testDeletingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentRepository($this->failedConnection(), new NullLogger());
        $repository->deleteByReportId(1);
    }

    private function setUpReports(...$ids)
    {
        $repository = new DoctrinePaymentReportRepository($this->connection, new NullLogger());

        foreach ($ids as $id) {
            $repository->save(new PaymentReport($id, PaymentReportStatus::createCalculated()));
        }
    }

    private static function payment(int $id): array
    {
        return [
            'event_id' => '100000000000000000000000000000'.$id,
            'event_type' => 'view',
            'status' => 0,
            'value' => 100,
        ];
    }
}
