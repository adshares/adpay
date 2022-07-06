<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\EventUpdateDTO;
use App\Application\DTO\ClickEventUpdateDTO;
use App\Domain\ValueObject\EventType;

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
