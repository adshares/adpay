<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InvalidArgumentException;
use App\Domain\ValueObject\EventType;
use App\Domain\ValueObject\Id;
use App\Domain\ValueObject\PaymentStatus;
use DateTimeInterface;

final class ConversionEvent extends Event
{
    /** @var Id */
    private $groupId;

    /** @var Id */
    private $conversionId;

    /** @var int */
    private $conversionValue;

    /** @var PaymentStatus */
    private $paymentStatus;

    public function __construct(
        Id $id,
        DateTimeInterface $time,
        ImpressionCase $case,
        Id $groupId,
        Id $conversionId,
        int $value,
        PaymentStatus $paymentStatus = null
    ) {
        parent::__construct($id, EventType::createConversion(), $time, $case);

        if ($value < 0) {
            throw InvalidArgumentException::fromArgument(
                'value',
                (string)$value,
                'The value must be greater than or equal to 0'
            );
        }

        if ($paymentStatus === null) {
            $paymentStatus = new PaymentStatus();
        }

        $this->groupId = $groupId;
        $this->conversionId = $conversionId;
        $this->conversionValue = $value;
        $this->paymentStatus = $paymentStatus;
    }

    public function getGroupId(): Id
    {
        return $this->groupId;
    }

    public function getConversionId(): Id
    {
        return $this->conversionId;
    }

    public function getConversionValue(): int
    {
        return $this->conversionValue;
    }

    public function getPaymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }
}
