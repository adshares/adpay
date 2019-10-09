<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Service\EventUpdater;
use Adshares\AdPay\Domain\Model\EventCollection;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

class DoctrineEventUpdater implements EventUpdater
{
    /*  @var Connection */
    private $db;

    /* @var LoggerInterface */
    private $logger;

    public function __construct(Connection $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function updateViews(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $views
    ): int {
        return 0;
    }

    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $click
    ): int {
        return 0;
    }

    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $conversions
    ): int {
        return 0;
    }
}
