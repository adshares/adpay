<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Service\EventUpdater;
use Adshares\AdPay\Domain\Model\ClickEventCollection;
use Adshares\AdPay\Domain\Model\ConversionEventCollection;
use Adshares\AdPay\Domain\Model\ViewEventCollection;
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
        ViewEventCollection $views
    ): int {
        return 0;
    }

    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        ClickEventCollection $click
    ): int {
        return 0;
    }

    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        ConversionEventCollection $conversions
    ): int {
        return 0;
    }
}
