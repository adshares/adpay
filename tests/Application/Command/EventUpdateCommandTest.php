<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\Command;

use Adshares\AdPay\Application\Command\EventUpdateCommand;
use Adshares\AdPay\Application\DTO\ClickEventUpdateDTO;
use Adshares\AdPay\Application\DTO\ViewEventUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Exception\InvalidDataException;
use Adshares\AdPay\Domain\Model\PaymentReport;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\PaymentReportStatus;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class EventUpdateCommandTest extends TestCase
{
    public function testExecuteCommand()
    {
        $timestamp = (int)floor(time() / 3600) * 3600 - 7200;

        $dto = new ViewEventUpdateDTO(
            [
                'time_start' => $timestamp + 12,
                'time_end' => $timestamp + 32,
                'events' => [],
            ]
        );

        $report = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->once())->method('saveAll')->with($dto->getEvents())->willReturn(100);

        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository->expects($this->once())->method('fetch')->with($timestamp)->willReturn($report);
        $paymentReportRepository->expects($this->once())->method('save')->with($report);

        /** @var EventRepository $eventRepository */
        /** @var PaymentReportRepository $paymentReportRepository */
        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());
        $this->assertEquals(100, $command->execute($dto));
        $this->assertEquals([[12, 32]], $report->getTypedIntervals($dto->getEvents()->getType()));
    }

    public function testExecuteCrossCommand()
    {
        $timestamp = (int)floor(time() / 3600) * 3600 - 7200;
        $report = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());

        $viewDto = new ViewEventUpdateDTO(
            [
                'time_start' => $timestamp + 12,
                'time_end' => $timestamp + 32,
                'events' => [],
            ]
        );

        $viewDto2 = new ViewEventUpdateDTO(
            [
                'time_start' => $timestamp + 2000,
                'time_end' => $timestamp + 2055,
                'events' => [],
            ]
        );

        $clickDto = new ClickEventUpdateDTO(
            [
                'time_start' => $timestamp + 30,
                'time_end' => $timestamp + 31,
                'events' => [],
            ]
        );

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository
            ->expects($this->exactly(3))
            ->method('saveAll')
            ->withConsecutive([$viewDto->getEvents()], [$viewDto2->getEvents()], [$clickDto->getEvents()])
            ->willReturn(100, 200, 300);

        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository->expects($this->exactly(3))->method('fetch')->with($timestamp)->willReturn($report);
        $paymentReportRepository->expects($this->exactly(3))->method('save')->with($report);

        /** @var EventRepository $eventRepository */
        /** @var PaymentReportRepository $paymentReportRepository */
        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());
        $this->assertEquals(100, $command->execute($viewDto));
        $this->assertEquals([[12, 32]], $report->getTypedIntervals(EventType::createView()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createClick()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createConversion()));

        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());
        $this->assertEquals(200, $command->execute($viewDto2));
        $this->assertEquals([[12, 32], [2000, 2055]], $report->getTypedIntervals(EventType::createView()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createClick()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createConversion()));

        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());
        $this->assertEquals(300, $command->execute($clickDto));
        $this->assertEquals([[12, 32], [2000, 2055]], $report->getTypedIntervals(EventType::createView()));
        $this->assertEquals([[30, 31]], $report->getTypedIntervals(EventType::createClick()));
        $this->assertEmpty($report->getTypedIntervals(EventType::createConversion()));
    }

    public function testExecuteWideCommand()
    {
        $timestamp = (int)floor(time() / 3600) * 3600 - 14400;

        $report1 = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());
        $report2 = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());
        $report3 = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());
        $report4 = new PaymentReport($timestamp, PaymentReportStatus::createIncomplete());

        $dto = new ClickEventUpdateDTO(
            [
                'time_start' => $timestamp - 1,
                'time_end' => $timestamp + 7250,
                'events' => [],
            ]
        );

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->expects($this->once())->method('saveAll')->with($dto->getEvents())->willReturn(300);

        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);
        $paymentReportRepository
            ->expects($this->exactly(4))
            ->method('fetch')
            ->withConsecutive([$timestamp - 3600], [$timestamp], [$timestamp + 3600], [$timestamp + 7200])
            ->willReturn($report1, $report2, $report3, $report4);

        $paymentReportRepository
            ->expects($this->exactly(4))
            ->method('save')
            ->withConsecutive([$report1], [$report2], [$report3], [$report4]);

        /** @var EventRepository $eventRepository */
        /** @var PaymentReportRepository $paymentReportRepository */
        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());
        $this->assertEquals(300, $command->execute($dto));
        $this->assertEquals([[3599, 3599]], $report1->getTypedIntervals($dto->getEvents()->getType()));
        $this->assertEquals([[0, 3599]], $report2->getTypedIntervals($dto->getEvents()->getType()));
        $this->assertEquals([[0, 3599]], $report3->getTypedIntervals($dto->getEvents()->getType()));
        $this->assertEquals([[0, 50]], $report4->getTypedIntervals($dto->getEvents()->getType()));
    }

    public function testInvalidData()
    {
        $this->expectException(ValidationException::class);

        $eventRepository = $this->createMock(EventRepository::class);
        $eventRepository->method('saveAll')->willThrowException(new InvalidDataException());
        $paymentReportRepository = $this->createMock(PaymentReportRepository::class);

        /** @var EventRepository $eventRepository */
        /** @var PaymentReportRepository $paymentReportRepository */
        $command = new EventUpdateCommand($eventRepository, $paymentReportRepository, new NullLogger());

        $timestamp = (int)floor(time() / 3600) * 3600 - 7200;

        $dto = new ViewEventUpdateDTO(
            [
                'time_start' => $timestamp + 12,
                'time_end' => $timestamp + 22,
                'events' => [],
            ]
        );

        $command->execute($dto);
    }
}
