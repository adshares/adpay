<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

class Budget
{
    /** @var int */
    private $value;

    /** @var int|null */
    private $maxCpm;

    /** @var int|null */
    private $maxCpc;

    public function __construct(int $value, ?int $maxCpm = null, ?int $maxCpc = null)
    {
        if ($value <= 0) {
            throw InvalidArgumentException::fromArgument('budget', (string)$value, 'The value must be greater than 0');
        }

        if ($maxCpm !== null && $maxCpm < 0) {
            throw InvalidArgumentException::fromArgument(
                'max CPM',
                (string)$maxCpm,
                'The value must be greater than or equal to 0'
            );
        }

        if ($maxCpc !== null && $maxCpc < 0) {
            throw InvalidArgumentException::fromArgument(
                'max CPC',
                (string)$maxCpc,
                'The value must be greater than or equal to 0'
            );
        }

        $this->value = $value;
        $this->maxCpm = $maxCpm;
        $this->maxCpc = $maxCpc;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getMaxCpm(): ?int
    {
        return $this->maxCpm;
    }

    public function getMaxCpc(): ?int
    {
        return $this->maxCpc;
    }

    public function toString(): string
    {
        return sprintf('%d [%s/%s]', $this->value, $this->maxCpm ?? '-', $this->maxCpc ?? '-');
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
