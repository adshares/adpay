<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;

final class Payment
{
    /** @var Id */
    private $id;

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
        Id $id,
        int $reportId,
        EventType $eventType,
        Id $eventId,
        PaymentStatus $status = null,
        ?int $value = null
    ) {
        $this->id = $id;
        $this->reportId = $reportId;
        $this->eventType = $eventType;
        $this->eventId = $eventId;
        $this->status = $status;
        $this->value = $value;
    }

    public function getId(): Id
    {
        return $this->id;
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

    public function getValue()
    {
        return $this->value;
    }
}
