<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Exception\InvalidDataException;
use Adshares\AdPay\Application\Exception\UpdateDataException;
use Adshares\AdPay\Application\Service\EventUpdater;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ClickEventMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ConversionEventMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\EventMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ViewEventMapper;
use DateTimeInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class DoctrineEventUpdater extends DoctrineModelUpdater implements EventUpdater
{
    public function updateViews(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $views
    ): int {
        return $this->insertEvents($timeStart, $timeEnd, $views, ViewEventMapper::class);
    }

    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $clicks
    ): int {
        return $this->insertEvents($timeStart, $timeEnd, $clicks, ClickEventMapper::class);
    }

    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $conversions
    ): int {
        return $this->insertEvents($timeStart, $timeEnd, $conversions, ConversionEventMapper::class);
    }

    private function insertEvents(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $events,
        string $mapper
    ): int {
        /*  @var $mapper EventMapper */
        $count = 0;
        try {
            $this->clearInterval($mapper::table(), $timeStart, $timeEnd);
            foreach ($events as $event) {
                /*  @var $event Event */
                try {
                    $this->db->insert(
                        $mapper::table(),
                        $mapper::map($event),
                        $mapper::types()
                    );
                } catch (UniqueConstraintViolationException $exception) {
                    throw new InvalidDataException(sprintf('Duplicate event id [%s]', $event->getId()));
                }
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        return $count;
    }
}
