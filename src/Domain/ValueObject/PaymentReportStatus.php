<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidArgumentException;

final class PaymentReportStatus
{
    public const CALCULATED = 0;

    public const INCOMPLETE = 1;

    public const COMPLETE = 2;

    private static $labels = [
        self::CALCULATED => 'calculated',
        self::INCOMPLETE => 'incomplete',
        self::COMPLETE => 'complete',
    ];

    /** @var int */
    private $status;

    public function __construct(int $status = self::INCOMPLETE)
    {
        if ($status !== self::CALCULATED && $status !== self::INCOMPLETE && $status !== self::COMPLETE) {
            throw InvalidArgumentException::fromArgument('status', (string)$status);
        }
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public static function createCalculated(): self
    {
        return new self(self::CALCULATED);
    }

    public static function createIncomplete(): self
    {
        return new self(self::INCOMPLETE);
    }

    public static function createComplete(): self
    {
        return new self(self::COMPLETE);
    }

    public function isCalculated(): bool
    {
        return $this->status === self::CALCULATED;
    }

    public function isIncomplete(): bool
    {
        return $this->status === self::INCOMPLETE;
    }

    public function isComplete(): bool
    {
        return $this->status === self::COMPLETE || $this->isCalculated();
    }

    public function toString(): string
    {
        return self::$labels[$this->status];
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
