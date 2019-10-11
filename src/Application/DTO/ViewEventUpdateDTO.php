<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;

final class ViewEventUpdateDTO extends EventUpdateDTO
{
    protected function createEventModel(array $input): Event
    {
        return new ViewEvent(
            new Id($input['id']),
            DateTimeHelper::createFromTimestamp($input['time']),
            $this->createImpressionCaseModel($input),
            new PaymentStatus($input['payment_status'] ?? null)
        );
    }
}