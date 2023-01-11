<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Application\DTO\EventUpdateDTO;
use App\Application\Exception\ValidationException;
use App\Domain\Exception\InvalidDataException;
use App\Domain\Repository\EventRepository;
use App\Domain\Repository\PaymentReportRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\LockableTrait;

final class EventUpdateCommand
{
    use LockableTrait;

    private const HOUR = 3600;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly PaymentReportRepository $paymentReportRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(EventUpdateDTO $dto): int
    {
        try {
            $result = $this->eventRepository->saveAll($dto->getEvents());
        } catch (InvalidDataException $exception) {
            throw new ValidationException($exception->getMessage());
        }

        $noticeStart = $dto->getEvents()->getTimeStart()->getTimestamp();
        $noticeEnd = $dto->getEvents()->getTimeEnd()->getTimestamp();
        $type = $dto->getEvents()->getType();

        $timestamp = (int)floor($noticeStart / self::HOUR) * self::HOUR;
        while ($timestamp <= $noticeEnd) {
            $start = max(0, $noticeStart - $timestamp);
            $end = min(self::HOUR - 1, $noticeEnd - $timestamp);

            $this->lock("PaymentReport.{$timestamp}", true);
            $report = $this->paymentReportRepository->fetchOrCreate($timestamp);
            $report->addInterval($type, $start, $end);
            $this->paymentReportRepository->save($report);
            $this->release();

            $timestamp += self::HOUR;
        }

        return $result;
    }
}
