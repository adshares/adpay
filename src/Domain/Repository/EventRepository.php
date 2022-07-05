<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Exception\InvalidDataException;
use App\Domain\Model\EventCollection;
use App\Domain\ValueObject\EventType;
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
