<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

final class Payment
{
    /** @var int */
    private $reportId;

    /** @var EventType */
    private $eventType;

    /** @var Id */
    private $eventId;

    /** @var PaymentStatus */
    private $status;

    /** @var ?int */
    private $value;

    public function __construct(
        int $reportId,
        EventType $eventType,
        Id $eventId,
        PaymentStatus $status,
        ?int $value = null
    ) {
        $this->reportId = $reportId;
        $this->eventType = $eventType;
        $this->eventId = $eventId;
        $this->status = $status;
        $this->value = $value;
    }

    public function getReportId(): int
    {
        return $this->reportId;
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

    public function getValue()
    {
        return $this->value;
    }
}
