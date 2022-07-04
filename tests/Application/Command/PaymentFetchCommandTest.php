<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\PaymentFetchCommand;
use App\Application\Exception\FetchingException;
use App\Domain\Model\PaymentReport;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\Repository\PaymentRepository;
use App\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class PaymentFetchCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $reportId = 1571403600;
        $paymentReportRepository = $this->paymentReportRepository($reportId);
        $paymentRepository = $this->paymentRepository($reportId);

        $command = new PaymentFetchCommand($paymentReportRepository, $paymentRepository, new NullLogger());
        $dto = $command->execute($reportId + 22);

        $this->assertTrue($dto->isCalculated());
        $this->assertCount(2, $dto->getPayments());
    }

    public function testPadding()
    {
        $reportId = 1571403600;
        $paymentReportRepository = $this->paymentReportRepository($reportId);
        $paymentRepository = $this->paymentRepository($reportId, 100, 200);

        $command = new PaymentFetchCommand($paymentReportRepository, $paymentRepository, new NullLogger());
        $dto = $command->execute($reportId + 22, 100, 200);

        $this->assertTrue($dto->isCalculated());
        $this->assertCount(2, $dto->getPayments());
    }

    public function testNotCalculated()
    {
        $reportId = 1571403600;
        $paymentReportRepository = $this->paymentReportRepository($reportId, PaymentReportStatus::COMPLETE);
        $paymentRepository = $this->emptyPaymentRepository();

        $command = new PaymentFetchCommand($paymentReportRepository, $paymentRepository, new NullLogger());
        $dto = $command->execute($reportId + 22);

        $this->assertFalse($dto->isCalculated());
        $this->assertEmpty($dto->getPayments());
    }

    public function testIncomplete()
    {
        $this->expectException(FetchingException::class);

        $reportId = 1571403600;
        $paymentReportRepository = $this->paymentReportRepository($reportId, PaymentReportStatus::INCOMPLETE);
        $paymentRepository = $this->emptyPaymentRepository();

        $command = new PaymentFetchCommand($paymentReportRepository, $paymentRepository, new NullLogger());
        $command->execute($reportId + 22);
    }

    private function paymentReportRepository(
        int $id,
        int $status = PaymentReportStatus::CALCULATED
    ): PaymentReportRepository {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetch')
            ->with($id)
            ->willReturn(new PaymentReport($id, new PaymentReportStatus($status)));

        /** @var PaymentReportRepository $repository */
        return $repository;
    }

    private function paymentRepository(int $reportId, ?int $limit = null, ?int $offset = null): PaymentRepository
    {
        $repository = $this->createMock(PaymentRepository::class);
        $repository->expects($this->once())
            ->method('fetchByReportId')
            ->with($reportId, $limit, $offset)
            ->willReturn([self::payment(1), self::payment(2)]);

        /** @var PaymentRepository $repository */
        return $repository;
    }

    private function emptyPaymentRepository(): PaymentRepository
    {
        $repository = $this->createMock(PaymentRepository::class);
        $repository->expects($this->never())
            ->method('fetchByReportId');

        /** @var PaymentRepository $repository */
        return $repository;
    }

    private static function payment(int $id): array
    {
        return [
            'id' => $id,
            'report_id' => 123,
            'event_id' => 'aac567e1396b4cadb52223a51796fdb' . $id,
            'event_type' => 'view',
            'status' => 1,
            'value' => 10000,
        ];
    }
}
