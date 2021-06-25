<?php

declare(strict_types=1);

namespace Adshares\AdPay\Domain\Repository;

use Adshares\AdPay\Domain\Exception\InvalidDataException;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\ValueObject\EventType;
use DateTimeInterface;

interface EventRepository
{
    public function fetchByTime(
        ?DateTimeInterface $timeStart = null,
        ?DateTimeInterface $timeEnd = null
    ): iterable;

    /**
     * @param EventCollection $events
     *
     * @return int
     * @throws InvalidDataException
     */
    public function saveAll(EventCollection $events): int;

    public function deleteByTime(
        EventType $type,
        ?DateTimeInterface $timeStart = null,
        ?DateTimeInterface $timeEnd = null
    ): int;
}
