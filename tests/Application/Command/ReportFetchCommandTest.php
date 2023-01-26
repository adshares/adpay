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
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchById')
            ->with(100, 101)
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        $this->assertEquals([100, 101], $command->execute(100, 101)->getReportIds());
    }

    public function testExecuteEmptyCommand()
    {
        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                new PaymentReportCollection(self::paymentReport(100), self::paymentReport(101))
            );

        /** @var PaymentReportRepository $repository */
        $command = new ReportFetchCommand($repository, new NullLogger());

        $this->assertEquals([100, 101], $command->execute()->getReportIds());
    }

    private static function paymentReport(int $id): PaymentReport
    {
        return new PaymentReport($id, new PaymentReportStatus());
    }
}
