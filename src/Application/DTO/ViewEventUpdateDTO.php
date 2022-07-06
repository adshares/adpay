<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Model\Event;
use App\Domain\Model\EventCollection;
use App\Domain\Model\ViewEvent;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Lib\DateTimeHelper;
use DateTimeInterface;

final class ViewEventUpdateDTO extends EventUpdateDTO
{
    protected function createEventCollection(DateTimeInterface $timeStart, DateTimeInterface $timeEnd): EventCollection
    {
        return new EventCollection(EventType::createView(), $timeStart, $timeEnd);
    }

    protected function createEventModel(array $input): Event
    {
        return new ViewEvent(
            new Id($input['id']),
            DateTimeHelper::fromTimestamp($input['time']),
            $this->createImpressionCaseModel($input)
        );
    }
}
