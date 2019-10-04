<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

final class Limit
{
    /** @var ?int */
    private $value;

    /** @var LimitType */
    private $type;

    /** @var int */
    private $cost;

    public function __construct(?int $value, LimitType $type, int $cost = 0)
    {
        if ($value !== null && $value < 0) {
            throw InvalidArgumentException::fromArgument(
                'limit',
                (string)$value,
                'The value must be greater than or equal to 0'
            );
        }

        if ($cost < 0) {
            throw InvalidArgumentException::fromArgument(
                'cost',
                (string)$cost,
                'The value must be greater than or equal to 0'
            );
        }

        $this->value = $value;
        $this->type = $type;
        $this->cost = $cost;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getType(): LimitType
    {
        return $this->type;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

    public function toString(): string
    {
        return sprintf('%s [%s]', $this->value ?? '-', $this->type);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
