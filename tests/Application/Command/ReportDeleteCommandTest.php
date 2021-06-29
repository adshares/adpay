<?php

declare(strict_types=1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\ReportDeleteCommand;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReportDeleteCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $date = new DateTime('2019-01-01 12:00:00');

        $repository = $this->createMock(PaymentReportRepository::class);
        $repository->expects($this->once())->method('deleteByTime')->with(null, $date)->willReturn(100);

        /** @var PaymentReportRepository $repository */
        $command = new ReportDeleteCommand($repository, new NullLogger());
        $this->assertEquals(100, $command->execute($date));
    }
}
