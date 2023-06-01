<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PaymentStatus;

final class Payment
{
    private EventType $eventType;

    private Id $eventId;

    private PaymentStatus $status;

    private ?int $value;

    private ?int $reportId;

    public function __construct(
        EventType $eventType,
        Id $eventId,
        PaymentStatus $status,
        ?int $value = null,
        ?int $reportId = null
    ) {
        $this->eventType = $eventType;
        $this->eventId = $eventId;
        $this->status = $status;
        $this->value = $value;
        $this->reportId = $reportId;
    }

    public function getReportId(): int
    {
        if ($this->reportId === null) {
            throw InvalidArgumentException::fromArgument('reportId', 'null');
        }

        return $this->reportId;
    }

    public function setReportId(int $reportId): void
    {
        $this->reportId = $reportId;
    }

    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    public function getEventId(): Id
    {
        return $this->eventId;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function getStatusCode(): int
    {
        return $this->status->getStatus();
    }

    public function isAccepted(): bool
    {
        return $this->status->isAccepted();
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
