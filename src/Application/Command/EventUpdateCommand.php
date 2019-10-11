<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Exception\InvalidDataException;
use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\Repository\PaymentReportRepository;
use Psr\Log\LoggerInterface;

final class EventUpdateCommand
{
    private const HOUR = 3600;

    /** @var EventRepository */
    private $eventRepository;

    /** @var PaymentReportRepository */
    private $paymentReportRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EventRepository $eventRepository,
        PaymentReportRepository $paymentReportRepository,
        LoggerInterface $logger
    ) {
        $this->eventRepository = $eventRepository;
        $this->paymentReportRepository = $paymentReportRepository;
        $this->logger = $logger;
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

        $timestamp = (int)floor($noticeStart / self::HOUR) * self::HOUR;
        while ($timestamp <= $noticeEnd) {
            $start = min(0, $noticeStart - $timestamp);
            $end = max(59, $noticeEnd - $timestamp);

            $report = $this->paymentReportRepository->fetch($timestamp);
//            $report->addInterval($start, $end);
//            $this->paymentReportRepository->update($report);

            $timestamp += self::HOUR;
        };

        return $result;
    }
}
