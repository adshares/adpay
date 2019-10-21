<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

final class Payment
{
    /** @var EventType */
    private $eventType;

    /** @var Id */
    private $eventId;

    /** @var PaymentStatus */
    private $status;

    /** @var ?int */
    private $value;

    /** @var int */
    private $reportId;

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
        if ($reportId !== null) {
            $this->reportId = $reportId;
        }
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

    public function getValue()
    {
        return $this->value;
    }
}
