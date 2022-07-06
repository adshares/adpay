<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\ReportFetchCommand;
use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReportFetchCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $command = $this->reportFetchCommand1(PaymentReportStatus::CALCULATED);
        $this->assertEquals([100, 101], $command->execute()->getReportIds());

        $command = $this->reportFetchCommand1(PaymentReportStatus::CALCULATED);
        $this->assertEquals([100, 101], $command->execute(true, false, false)->getReportIds());

        $command = $this->reportFetchCommand1(PaymentReportStatus::COMPLETE);
        $this->assertEquals([100, 101], $command->execute(false, true, false)->getReportIds());

        $command = $this->reportFetchCommand1(PaymentReportStatus::INCOMPLETE);
        $this->assertEquals([100, 101], $command->execute(false, false, true)->getReportIds());

        $command = $this->reportFetchCommand2(
            PaymentReportStatus::CALCULATED,
            PaymentReportStatus::COMPLETE
        );
        $this->assertEquals([100, 101], $command->execute(true, true, false)->getReportIds());

        $command = $this->reportFetchCommand2(
            PaymentReportStatus::CALCULATED,
            PaymentReportStatus::INCOMPLETE
        );
        $this->assertEquals([100, 101], $command->execute(true, false, true)->getReportIds());

        $command = $this->reportFetchCommand2(
            PaymentReportStatus::COMPLETE,
            PaymentReportStatus::INCOMPLETE
        );
        $this->assertEquals([100, 101], $command->execute(false, true, true)->getReportIds());

        $command = $this->reportFetchCommand3(
            PaymentReportStatus::CALCULATED,
            PaymentReportStatus::COMPLETE,
            PaymentReportStatus::INCOMPLETE
        );
        $this->assertEquals([100, 101], $command->execute(true, true, true)->getReportIds());
    }

    public function testExecuteEmptyCommand()
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchByStatus')
            ->with()
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        $this->assertEquals([100, 101], $command->execute(false, false, false)->getReportIds());
    }

    private function reportFetchCommand1(int $status1): ReportFetchCommand
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchByStatus')
            ->with(
                $this->callback(
                    function (PaymentReportStatus $item) use ($status1) {
                        return $item->getStatus() === $status1;
                    }
                )
            )
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        return $command;
    }

    private function reportFetchCommand2(int $status1, int $status2): ReportFetchCommand
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchByStatus')
            ->with(
                $this->callback(
                    function (PaymentReportStatus $item) use ($status1) {
                        return $item->getStatus() === $status1;
                    }
                ),
                $this->callback(
                    function (PaymentReportStatus $item) use ($status2) {
                        return $item->getStatus() === $status2;
                    }
                )
            )
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        return $command;
    }

    private function reportFetchCommand3(int $status1, int $status2, int $status3): ReportFetchCommand
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchByStatus')
            ->with(
                $this->callback(
                    function (PaymentReportStatus $item) use ($status1) {
                        return $item->getStatus() === $status1;
                    }
                ),
                $this->callback(
                    function (PaymentReportStatus $item) use ($status2) {
                        return $item->getStatus() === $status2;
                    }
                ),
                $this->callback(
                    function (PaymentReportStatus $item) use ($status3) {
                        return $item->getStatus() === $status3;
                    }
                )
            )
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        return $command;
    }

    private static function paymentReport(int $id): PaymentReport
    {
        return new PaymentReport($id, new PaymentReportStatus());
    }
}
