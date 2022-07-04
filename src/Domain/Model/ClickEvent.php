<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use DateTimeInterface;

final class ClickEvent extends Event
{
    public function __construct(Id $id, DateTimeInterface $time, ImpressionCase $case)
    {
        parent::__construct($id, EventType::createClick(), $time, $case);
    }
}
