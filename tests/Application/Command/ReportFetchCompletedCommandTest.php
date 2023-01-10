<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\ReportFetchCompletedCommand;
use App\Domain\Model\PaymentReport;
use App\Domain\Model\PaymentReportCollection;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReportFetchCompletedCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $command = $this->reportFetchCommand();
        $this->assertEquals([100, 101], $command->execute()->getReportIds());
    }

    private function reportFetchCommand(): ReportFetchCompletedCommand
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchByStatus')
            ->with(
                $this->callback(
                    function (PaymentReportStatus $item) {
                        return $item->getStatus() === PaymentReportStatus::COMPLETE;
                    }
                )
            )
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        return new ReportFetchCompletedCommand($repository, new NullLogger());
    }

    private static function paymentReport(int $id): PaymentReport
    {
        return new PaymentReport($id, new PaymentReportStatus());
    }
}
