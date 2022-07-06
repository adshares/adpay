<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\EventUpdateDTO;
use App\Application\DTO\ViewEventUpdateDTO;
use App\Domain\ValueObject\EventType;

final class ViewEventUpdateDTOTest extends EventUpdateDTOTest
{
    protected function getEventType(): EventType
    {
        return EventType::createView();
    }

    protected function createDTO(array $data): EventUpdateDTO
    {
        return new ViewEventUpdateDTO($data);
    }
}
