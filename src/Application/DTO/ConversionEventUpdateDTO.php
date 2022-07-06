<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Exception\ValidationException;
use App\Domain\Model\ConversionEvent;
use App\Domain\Model\Event;
use App\Domain\Model\EventCollection;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PaymentStatus;
use App\Lib\DateTimeHelper;
use DateTimeInterface;

final class ConversionEventUpdateDTO extends EventUpdateDTO
{
    protected function createEventCollection(DateTimeInterface $timeStart, DateTimeInterface $timeEnd): EventCollection
    {
        return new EventCollection(EventType::createConversion(), $timeStart, $timeEnd);
    }

    protected function createEventModel(array $input): Event
    {
        return new ConversionEvent(
            new Id($input['id']),
            DateTimeHelper::fromTimestamp($input['time']),
            $this->createImpressionCaseModel($input),
            new Id($input['group_id']),
            new Id($input['conversion_id']),
            $input['conversion_value'],
            new PaymentStatus($input['payment_status'] ?? null)
        );
    }

    protected function validateEvent(array $input): void
    {
        parent::validateEvent($input);

        if (!isset($input['group_id'])) {
            throw new ValidationException('Field `group_id` is required.');
        }
        if (!isset($input['conversion_id'])) {
            throw new ValidationException('Field `conversion_id` is required.');
        }
        if (!isset($input['conversion_value'])) {
            throw new ValidationException('Field `conversion_value` is required.');
        }
    }
}
