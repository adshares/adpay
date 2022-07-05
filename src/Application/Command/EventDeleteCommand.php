<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Repository\EventRepository;
use App\Domain\ValueObject\EventType;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class EventDeleteCommand
{
    private EventRepository $eventRepository;

    private LoggerInterface $logger;

    public function __construct(
        EventRepository $eventRepository,
        LoggerInterface $logger
    ) {
        $this->eventRepository = $eventRepository;
        $this->logger = $logger;
    }

    public function execute(DateTimeInterface $dateTo): int
    {
        $this->logger->debug('Running delete events command');
        $views = $this->eventRepository->deleteByTime(EventType::createView(), null, $dateTo);
        $this->logger->info(sprintf('%d views deleted', $views));
        $clicks = $this->eventRepository->deleteByTime(EventType::createClick(), null, $dateTo);
        $this->logger->info(sprintf('%d clicks deleted', $clicks));
        $conversions = $this->eventRepository->deleteByTime(EventType::createConversion(), null, $dateTo);
        $this->logger->info(sprintf('%d conversions deleted', $conversions));

        return $views + $clicks + $conversions;
    }
}
