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
        $this->installReports(1, 2, 3, 4);
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

        $list = [];
        array_push($list, ...$repository->fetchByReportId(1));
        $this->assertCount(5, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(2));
        $this->assertCount(2, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(3));
        $this->assertCount(3, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(4));
        $this->assertEmpty($list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(5));
        $this->assertEmpty($list);
    }

    public function testPagination(): void
    {
        $this->installReports(1);
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

        $list = [];
        array_push($list, ...$repository->fetchByReportId(1, 3));
        $this->assertCount(3, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(1, 3, 3));
        $this->assertCount(3, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(1, 3, 6));
        $this->assertCount(1, $list);

        $list = [];
        array_push($list, ...$repository->fetchByReportId(1, 3, 9));
        $this->assertEmpty($list);
    }

    public function testDeleting(): void
    {
        $this->installReports(2);
        $repository = new DoctrinePaymentRepository($this->connection, new NullLogger());

        $repository->saveAllRaw(
            2,
            [
                self::payment(22),
                self::payment(23),
            ]
        );

        $this->assertEquals(0, $repository->deleteByReportId(123));

        $list = [];
        array_push($list, ...$repository->fetchByReportId(2));
        $this->assertCount(2, $list);

        $this->assertEquals(2, $repository->deleteByReportId(2));

        $list = [];
        array_push($list, ...$repository->fetchByReportId(2));
        $this->assertEmpty($list);
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
        $list = [];
        array_push($list, ...$repository->fetchByReportId(2));
    }

    public function testDeletingException(): void
    {
        $this->expectException(DomainRepositoryException::class);

        $repository = new DoctrinePaymentRepository($this->failedConnection(), new NullLogger());
        $repository->deleteByReportId(1);
    }

    private function installReports(...$ids)
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
