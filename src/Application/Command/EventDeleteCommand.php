<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\Command;

use Adshares\AdPay\Domain\Repository\EventRepository;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;
use Psr\Log\LoggerInterface;

final class EventDeleteCommand
{
    /** @var EventRepository */
    private $eventRepository;

    /** @var LoggerInterface */
    private $logger;

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
