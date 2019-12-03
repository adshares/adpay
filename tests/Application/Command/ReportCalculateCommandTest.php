<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\ReportCalculateCommand;
use Adshares\AdPay\Application\Exception\FetchingException;
use Adshares\AdPay\Domain\Model\CampaignCollection;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\CampaignRepository;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\Repository\PaymentRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
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
        $this->expectException(FetchingException::class);
        $report = new PaymentReport(1571659200, PaymentReportStatus::createIncomplete());

        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository->expects($this->once())
            ->method('fetch')
            ->with($report->getId())
            ->willReturn($report);
        $paymentReportRepository->expects($this->never())->method('save');

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository->expects($this->never())->method('deleteByReportId');
        $paymentRepository->expects($this->never())->method('saveAllRaw');

        $campaignRepository = $this->createMock(CampaignRepository::class);
        $campaignRepository->expects($this->never())->method('fetchAll');

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->never())->method('fetchByTime');

        /** @var PaymentReportRepository $paymentReportRepository */
        /** @var PaymentRepository $paymentRepository */
        /** @var CampaignRepository $campaignRepository */
        /** @var EventRepository $eventRepository */

        $command = new ReportCalculateCommand(
            $paymentReportRepository,
            $paymentRepository,
            $campaignRepository,
            $eventRepository,
            new NullLogger()
        );

        $command->execute(1571659222);
    }

    private function reportCalculateCommand(PaymentReport $report, array $events): ReportCalculateCommand
    {
        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository->expects($this->once())->method('fetch')->with($report->getId())->willReturn($report);
        $paymentReportRepository->expects($this->once())->method('save')->with($report);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $paymentRepository->expects($this->once())->method('deleteByReportId')->with($report->getId());
        $paymentRepository->expects($this->exactly((int)floor(count($events) / 1000) + 1))->method('saveAllRaw');

        $campaignRepository = $this->createMock(CampaignRepository::class);
        $campaignRepository->expects($this->once())->method('fetchAll')->willReturn(new CampaignCollection());

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->once())
            ->method('fetchByTime')
            ->with($report->getTimeStart(), $report->getTimeEnd())
            ->willReturn($events);

        /** @var PaymentReportRepository $paymentReportRepository */
        /** @var PaymentRepository $paymentRepository */
        /** @var CampaignRepository $campaignRepository */
        /** @var EventRepository $eventRepository */

        $command = new ReportCalculateCommand(
            $paymentReportRepository,
            $paymentRepository,
            $campaignRepository,
            $eventRepository,
            new NullLogger()
        );

        return $command;
    }

    private static function event(int $id): array
    {
        return [
            'id' => '1000000000000000000000000000000'.$id,
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
