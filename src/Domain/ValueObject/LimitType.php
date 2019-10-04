<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

class LimitType
{
    public const IN_BUDGET = 'in_budget';

    public const OUT_OF_BUDGET = 'out_of_budget';

    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        if ($type !== self::IN_BUDGET && $type !== self::OUT_OF_BUDGET) {
            throw InvalidArgumentException::fromArgument('type', $type);
        }

        $this->type = $type;
    }

    public static function createInBudget(): self
    {
        return new self(self::IN_BUDGET);
    }

    public static function createOutOfBudget(): self
    {
        return new self(self::OUT_OF_BUDGET);
    }

    public function isInBudget(): bool
    {
        return $this->type === self::IN_BUDGET;
    }

    public function isOutOfBudget(): bool
    {
        return $this->type === self::OUT_OF_BUDGET;
    }

    public function toString(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
