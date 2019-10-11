<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationException;
use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;
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
            DateTimeHelper::createFromTimestamp($input['time']),
            $this->createImpressionCaseModel($input),
            new Id($input['conversion_id']),
            $input['conversion_value'] ?? null,
            new PaymentStatus($input['payment_status'] ?? null)
        );
    }

    protected function validateEvent(array $input): void
    {
        parent::validateEvent($input);

        if (!isset($input['conversion_id'])) {
            throw new ValidationException('Field `conversion_id` is required.');
        }
    }
}
