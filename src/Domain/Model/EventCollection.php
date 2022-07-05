<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\EventType;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;

final class EventCollection extends ArrayCollection
{
    /** @var EventType */
    private $type;

    /** @var ?DateTimeInterface */
    private $timeStart;

    /** @var ?DateTimeInterface */
    private $timeEnd;

    public function __construct(
        EventType $type,
        DateTimeInterface $timeStart = null,
        DateTimeInterface $timeEnd = null,
        Event ...$views
    ) {
        if ($timeStart !== null && $timeEnd !== null && $timeEnd < $timeStart) {
            throw new InvalidArgumentException('End time cannot be greater than start time.');
        }

        parent::__construct($views);
        $this->type = $type;
        $this->timeStart = $timeStart;
        $this->timeEnd = $timeEnd;
    }

    /**
     * @return EventType
     */
    public function getType(): EventType
    {
        return $this->type;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getTimeStart(): ?DateTimeInterface
    {
        return $this->timeStart;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getTimeEnd(): ?DateTimeInterface
    {
        return $this->timeEnd;
    }
}
