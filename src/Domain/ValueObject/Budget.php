<?php declare(strict_types = 1);

namespace Adshares\AdPay\Domain\ValueObject;

use Adshares\AdPay\Domain\Exception\InvalidArgumentException;

final class Budget
{
    /** @var int */
    private $budget;

    /** @var int|null */
    private $maxCpm;

    /** @var int|null */
    private $maxCpc;

    public function __construct(int $budget, ?int $maxCpm = null, ?int $maxCpc = null)
    {
        if ($budget <= 0) {
            throw new InvalidArgumentException('budget', (string)$budget, 'The value must be greater than 0');
        }

        if ($maxCpm !== null && $maxCpm < 0) {
            throw new InvalidArgumentException('max CPM', (string)$budget, 'The value must be greater than 0');
        }

        if ($maxCpc !== null && $maxCpc < 0) {
            throw new InvalidArgumentException('max CPC', (string)$budget, 'The value must be greater than 0');
        }

        $this->budget = $budget;
        $this->maxCpm = $maxCpm;
        $this->maxCpc = $maxCpc;
    }

    public function getBudget(): int
    {
        return $this->budget;
    }

    public function getMaxCpm(): ?int
    {
        return $this->maxCpm;
    }

    public function getMaxCpc(): ?int
    {
        return $this->maxCpc;
    }
}
