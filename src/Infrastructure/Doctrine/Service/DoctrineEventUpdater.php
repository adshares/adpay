<?php declare(strict_types = 1);

namespace Adshares\AdPay\Infrastructure\Doctrine\Service;

use Adshares\AdPay\Application\Exception\UpdateDataException;
use Adshares\AdPay\Application\Service\EventUpdater;
use Adshares\AdPay\Domain\Model\ClickEvent;
use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ClickEventMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ConversionEventMapper;
use Adshares\AdPay\Infrastructure\Doctrine\Mapper\ViewEventMapper;
use DateTimeInterface;
use Doctrine\DBAL\DBALException;

class DoctrineEventUpdater extends DoctrineModelUpdater implements EventUpdater
{
    public function updateViews(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $views
    ): int {
        $count = 0;
        try {
            foreach ($views as $event) {
                /*  @var $event ViewEvent */
                $this->upsert(
                    ViewEventMapper::table(),
                    $event->getId(),
                    ViewEventMapper::map($event),
                    ViewEventMapper::types()
                );
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        return $count;
    }

    public function updateClicks(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $clicks
    ): int {
        $count = 0;
        try {
            foreach ($clicks as $event) {
                /*  @var $event ClickEvent */
                $this->upsert(
                    ClickEventMapper::table(),
                    $event->getId(),
                    ClickEventMapper::map($event),
                    ClickEventMapper::types()
                );
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        return $count;
    }

    public function updateConversions(
        DateTimeInterface $timeStart,
        DateTimeInterface $timeEnd,
        EventCollection $conversions
    ): int {
        $count = 0;
        try {
            foreach ($conversions as $event) {
                /*  @var $event ConversionEvent */
                $this->upsert(
                    ConversionEventMapper::table(),
                    $event->getId(),
                    ConversionEventMapper::map($event),
                    ConversionEventMapper::types()
                );
                ++$count;
            }
        } catch (DBALException $exception) {
            throw new UpdateDataException($exception->getMessage());
        }

        return $count;
    }
}
