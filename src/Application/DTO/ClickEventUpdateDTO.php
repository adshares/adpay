<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Domain\Model\ClickEvent;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;

final class ClickEventUpdateDTO extends EventUpdateDTO
{
    protected function createEventCollection(): EventCollection
    {
        return new EventCollection(EventType::createClick());
    }

    protected function createEventModel(array $input): Event
    {
        return new ClickEvent(
            new Id($input['id']),
            DateTimeHelper::createFromTimestamp($input['time']),
            $this->createImpressionCaseModel($input),
            new PaymentStatus($input['payment_status'] ?? null)
        );
    }
}
