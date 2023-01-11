<?php

declare(strict_types=1);

namespace App\Tests\Application\Command;

use App\Application\Command\ReportCalculateCommand;
use App\Application\Exception\ReportNotCompleteException;
use App\Domain\Model\PaymentReport;
use App\Domain\Repository\EventRepository;
use App\Domain\Repository\PaymentReportRepository;
use App\Domain\Repository\PaymentRepository;
use App\Domain\Service\PaymentCalculator;
use App\Domain\Service\PaymentCalculatorFactory;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\PaymentReportStatus;
use App\Domain\ValueObject\PaymentStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReportCalculateCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $report = new PaymentReport(1571659200, PaymentReportStatus::createComplete());
        $command = $this->reportCalculateCommand($report, []);
        $this->assertEquals(0, $command->execute(1571659222));

        $report = new PaymentReport(1571659200, PaymentReportStatus::createComplete());
        $command = $this->reportCalculateCommand($report, [self::event(1), self::event(2), self::event(3)]);
        $this->assertEquals(3, $command->execute(1571659222));
    }

    public function testBachInsert()
    {
        $events = [];
        for ($i = 0; $i < 1001; ++$i) {
            $events[] = self::event($i);
        }

        $report = new PaymentReport(1571659200, PaymentReportStatus::createComplete());
        $command = $this->reportCalculateCommand($report, $events);
        $this->assertEquals(1001, $command->execute(1571659222));
    }

    public function testIncompleteReport()
    {
        $this->expectException(ReportNotCompleteException::class);
        $report = new PaymentReport(1571659200, PaymentReportStatus::createIncomplete());

        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository->expects($this->once())
            ->method('fetchOrCreate')
            ->with($report->getId())
            ->willReturn($report);
        $paymentReportRepository->expects($this->never())->method('save');

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository->expects($this->never())->method('deleteByReportId');
        $paymentRepository->expects($this->never())->method('saveAllRaw');

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->never())->method('fetchByTime');

        $paymentCalculatorFactory = $this->createMock(PaymentCalculatorFactory::class);
        $paymentCalculatorFactory->expects($this->never())->method('createPaymentCalculator');

        /** @var PaymentReportRepository $paymentReportRepository */
        /** @var PaymentRepository $paymentRepository */
        /** @var EventRepository $eventRepository */
        /** @var PaymentCalculatorFactory $paymentCalculatorFactory */

        $command = new ReportCalculateCommand(
            $paymentReportRepository,
            $paymentRepository,
            $eventRepository,
            $paymentCalculatorFactory,
            new NullLogger()
        );

        $command->execute(1571659222);
    }

    private function reportCalculateCommand(PaymentReport $report, array $events): ReportCalculateCommand
    {
        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository
            ->expects($this->once())
            ->method('fetchOrCreate')
            ->with($report->getId())
            ->willReturn($report);
        $paymentReportRepository
            ->expects($this->once())
            ->method('save')
            ->with($report);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository
            ->expects($this->once())
            ->method('deleteByReportId')
            ->with($report->getId());
        $paymentRepository
            ->expects($this->exactly((int)floor(count($events) / 1000) + 1))
            ->method('saveAllRaw');

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->once())
            ->method('fetchByTime')
            ->with($report->getTimeStart(), $report->getTimeEnd())
            ->willReturn($events);

        $paymentCalculator = $this->createMock(PaymentCalculator::class);
        $paymentCalculator->expects($this->once())->method('calculate')->willReturnCallback(
            function ($reportId, $events) {
                foreach ($events as $event) {
                    yield [
                        'event_type' => $event['type'],
                        'event_id' => $event['id'],
                        'status' => PaymentStatus::CAMPAIGN_NOT_FOUND,
                        'value' => null,
                    ];
                }
            }
        );
        $paymentCalculatorFactory = $this->createMock(PaymentCalculatorFactory::class);
        $paymentCalculatorFactory
            ->expects($this->once())
            ->method('createPaymentCalculator')
            ->willReturn($paymentCalculator);

        /** @var PaymentReportRepository $paymentReportRepository */
        /** @var PaymentRepository $paymentRepository */
        /** @var EventRepository $eventRepository */
        /** @var PaymentCalculatorFactory $paymentCalculatorFactory */

        return new ReportCalculateCommand(
            $paymentReportRepository,
            $paymentRepository,
            $eventRepository,
            $paymentCalculatorFactory,
            new NullLogger()
        );
    }

    private static function event(int $id): array
    {
        return [
            'id' => '1000000000000000000000000000000' . $id,
            'type' => EventType::VIEW,
            'time' => '2019-10-21 08:08:49',
            'case_id' => '20000000000000000000000000000001',
            'case_time' => '2019-10-21 08:08:45',
            'publisher_id' => '30000000000000000000000000000001',
            'zone_id' => '40000000000000000000000000000001',
            'advertiser_id' => '50000000000000000000000000000001',
            'campaign_id' => '60000000000000000000000000000001',
            'banner_id' => '70000000000000000000000000000001',
            'impression_id' => '80000000000000000000000000000001',
            'tracking_id' => '90000000000000000000000000000001',
            'user_id' => 'a0000000000000000000000000000001',
            'page_rank' => 1.0,
            'human_score' => 0.9,
            'keywords' => ['r1' => ['r1_v1'], 'e1' => ['e1_v3']],
            'context' => [],
        ];
    }
}
