<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\Model;

use Adshares\AdPay\Domain\ValueObject\EventType;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Domain\ValueObject\PaymentStatus;
use DateTimeInterface;

final class ConversionEvent extends Event
{
    /** @var Id */
    private $conversionId;

    /** @var ?int */
    private $value;

    public function __construct(
        Id $id,
        DateTimeInterface $time,
        ImpressionCase $case,
        Id $conversionId,
        ?int $value,
        PaymentStatus $paymentStatus = null
    ) {
        parent::__construct($id, EventType::createConversion(), $time, $case, $paymentStatus);
        $this->conversionId = $conversionId;
        $this->value = $value;
    }

    public function getConversionId(): Id
    {
        return $this->conversionId;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
