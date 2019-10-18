<?php declare(strict_types = 1);

namespace Adshares\AdPay\Tests\Application\DTO;

use Adshares\AdPay\Application\DTO\EventUpdateDTO;
use Adshares\AdPay\Application\DTO\ClickEventUpdateDTO;
use Adshares\AdPay\Domain\ValueObject\EventType;

final class ClickEventUpdateDTOTest extends EventUpdateDTOTest
{
    protected function getEventType(): EventType
    {
        return EventType::createClick();
    }

    protected function createDTO(array $data): EventUpdateDTO
    {
        return new ClickEventUpdateDTO($data);
    }
}
