<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Model\ConversionEvent;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use Adshares\AdPay\Lib\DateTimeHelper;

final class ConversionEventUpdateDTO extends EventUpdateDTO
{
    protected function createEventModel(array $input): Event
    {
        return new ConversionEvent(
            new Id($input['id']),
            DateTimeHelper::createFromTimestamp($input['time']),
            $this->createImpressionCaseModel($input),
            new Id($input['conversion_id']),
            $input['value'] ?? null,
            new PaymentStatus($input['payment_status'] ?? null)
        );
    }

    protected function validateEvent(array $input): void
    {
        parent::validateEvent($input);

        if (!isset($input['conversion_id'])) {
            throw new ValidationDTOException('Field `conversion_id` is required.');
        }
    }
}
